<?php declare(strict_types=1);

namespace HRDNS\Protocol;

class IMAP
{

    private $timeout = 3;
    private $retry = 1;
    private $config = array();

    public function __construct(
        string $server,
        string $protocol,
        int $port,
        string $username = null,
        string $password = null,
        string $folder = 'INBOX'
    ) {
        $this->config['option'] = array('novalidate-cert' => true);
        $this->config['server'] = $server;
        $this->config['proto'] = $protocol;
        $this->config['port'] = $port;
        $this->config['user'] = $username;
        $this->config['pass'] = $password;
        $this->config['folder'] = $folder;
        $this->config['connect'] = null;
        $this->config['link'] = null;
    }

    public function __destruct()
    {
        $this->close();
    }

    private function createConnectionString(): string
    {
        $this->config['link'] = '';

        $result = '/' . $this->config['proto'];
        foreach ($this->config['option'] as $strOption => $strKey) {
            if ($strKey !== false && $strKey !== null) {
                $result .= '/' . $strOption;
            }
        }

        $result = sprintf(
            '{%s:%s%s}',
            $this->config['server'],
            $this->config['port'],
            $result
        );

        $this->config['link'] = $result;
        return $result;
    }

    public function open(): self
    {
        @imap_timeout($this->timeout);
        $this->config['connect'] = @imap_open(
            $this->createConnectionString() . $this->config['folder'],
            $this->config['user'],
            $this->config['pass'],
            0,
            $this->retry
        );
        if ($this->config['connect'] === false) {
            throw new \RuntimeException(@imap_last_error());
        } else {
            return $this;
        }
    }

    public function close(): self
    {
        if ($this->config['connect'] === null) {
            return $this;
        }
        @imap_close($this->config['connect']);
        $this->config['connect'] = null;
        return $this;
    }

    public function setOption(string $option, $value = null): self
    {
        $option = trim(strtolower($option));
        switch ($option) {
            case 'proto':
            case 'protocol':
            case 'protokol':
                $this->config['proto'] = $value;
                break;
            case 'imap':
            case 'pop3':
            case 'imap2':
            case 'imap2bis':
            case 'imap4':
            case 'imap4rev1':
            case 'nntp':
                $this->config['proto'] = $option;
                break;
            case 'user':
            case 'authuser':
            case 'anonymous':
            case 'secure':
            case 'norsh':
            case 'ssl':
            case 'validate-cert':
            case 'novalidate-cert':
            case 'tls':
            case 'notls':
            case 'readonly':
                $this->config['option'][$option] = $value;
                break;
            default:
                throw new \RuntimeException('Unknown option: ' . $option);
        }
        return $this;
    }

    public function switchFolder(string $folder): self
    {
        $this->config['folder'] = $folder;
        if (!@imap_reopen(
            $this->config['connect'],
            $this->createConnectionString() . $this->config['folder']
        )) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $this;
    }

    public function getQuota(string $folder = null): array
    {
        if ($folder === null) {
            $folder = $this->config['folder'];
        }
        $result = @imap_get_quotaroot($this->config['connect'], $folder);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        unset($result['STORAGE']);
        $result['percent'] = round($result['usage'] / $result['limit'] * 100, 0);
        return $result;
    }

    public function listFolders(): array
    {
        $result = array();
        $folders = @imap_list($this->config['connect'], $this->config['link'], '*');
        if (!is_array($folders)) {
            throw new \RuntimeException(@imap_last_error());
        }
        foreach ($folders as $folder) {
            $folder = str_replace($this->config['link'], '', $folder);
            if (trim($folder) != '') {
                $result[] = $folder;
            }
        }
        return $result;
    }

    public function listEmails(): array
    {
        $emails = @imap_headers($this->config['connect']);
        if ($emails === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $emails;
    }

    public function getFolderInformation(): array
    {
        $result = @imap_mailboxmsginfo($this->config['connect']);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return array(
            'date' => $result->Date,
            'driver' => $result->Driver,
            'mailbox' => $result->Mailbox,
            'messages' => $result->Nmsgs,
            'recent' => $result->Recent,
            'unread' => $result->Unread,
            'deleted' => $result->Deleted,
            'size' => $result->Size
        );
    }

    public function getUIDByMsgNo(int $messageNumber): int
    {
        $result = @imap_uid($this->config['connect'], $messageNumber);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $result;
    }

    public function getMsgNoByUID(int $uid): int
    {
        $result = @imap_msgno($this->config['connect'], $uid);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $result;
    }

    public function getMessageOverviewByUID(int $uid): array
    {
        $result = @imap_fetch_overview($this->config['connect'], (string)$uid, FT_UID);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return array(
            'subject' => $this->decodeString($result[0]->subject),
            'from' => $this->decodeString($result[0]->from),
            'to' => $this->decodeString($result[0]->to),
            'date' => $result[0]->date,
            'message_id' => $result[0]->message_id,
            'size' => $result[0]->size,
            'uid' => $result[0]->uid,
            'msgno' => $result[0]->msgno,
            'recent' => $result[0]->recent,
            'flagged' => $result[0]->flagged,
            'answered' => $result[0]->answered,
            'deleted' => $result[0]->deleted,
            'seen' => $result[0]->seen,
            'draft' => $result[0]->draft
        );
    }

    public function getMessageOverviewByMsgNo(int $messageNumber): array
    {
        return $this->getMessageOverviewByUID($this->getUIDByMsgNo($messageNumber));
    }

    public function getMessages(): array
    {
        $result = @imap_sort($this->config['connect'], SORTDATE, 0, SE_UID);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $result;
    }

    public function getMessageBodyByUID(int $uid): string
    {
        $result = @imap_body($this->config['connect'], $uid, FT_UID + FT_PEEK);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $result;
    }

    public function getMessageBodyByMsgNo(int $messageNumber): string
    {
        return $this->getMessageBodyByUID($this->getUIDByMsgNo($messageNumber));
    }

    public function getMessageStructureByUID(int $uid)
    {
        $result = @imap_fetchstructure($this->config['connect'], $uid, FT_UID);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $result;
    }

    public function getMessageStructureByMsgNo(int $messageNumber): array
    {
        return $this->getMessageStructureByUID($this->getUIDByMsgNo($messageNumber));
    }

    public function createFolder(string $folder): self
    {
        $folders = (array)$this->listFolders();
        if (!in_array($folder, $folders)) {
            $result = @imap_createmailbox(
                $this->config['connect'],
                @imap_utf7_encode($this->config['link'] . $folder)
            );
            if ($result === false) {
                throw new \RuntimeException(@imap_last_error());
            }
        }
        return $this;
    }

    public function deleteFolder(string $folder): self
    {
        $folders = $this->listFolders();
        if (in_array($folder, $folders)) {
            $result = @imap_deletemailbox(
                $this->config['connect'],
                @imap_utf7_encode($this->config['link'] . $folder)
            );
            if ($result === false) {
                throw new \RuntimeException(@imap_last_error());
            }
        }
        return $this;
    }

    public function copyMessageByUID(int $uid, string $folder, int $options = CP_UID): self
    {
        $result = @imap_mail_copy($this->config['connect'], (string)$uid, $folder, $options);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $this;
    }

    public function copyMessageByMsgNo(int $messageNumber, string $folder, int $options = CP_UID): self
    {
        return $this->copyMessageByUID($this->getUIDByMsgNo($messageNumber), $folder, $options);
    }

    public function moveMessageByUID(int $uid, string $folder, int $options = null): self
    {
        if ($options === null) {
            $options = CP_UID + CP_MOVE;
        }
        return $this->copyMessageByUID($uid, $folder, $options);
    }

    public function moveMessageByMsgNo(int $messageNumber, string $folder, int $options = null)
    {
        return $this->moveMessageByUID($this->getUIDByMsgNo($messageNumber), $folder, $options);
    }

    public function mail(string $to, string $subject = '', string $body = '', string $header = null): self
    {
        $header = trim($header);
        $header .= "\r\nX-HRDNS-Agent: HRDNS-IMAP-v.0.1.0";
        $header .= "\r\nX-HRDNS-User: " . $this->config['user'];
        $header .= "\r\nX-HRDNS-Server: " . $this->config['server'];
        if (strpos($header, 'Content-Type:') === false) {
            $header .= "\r\nContent-Type: text/plain; charset=UTF-8; format=flowed";
        }
        $header = trim($header);
        $result = @imap_mail($to, $subject, $body, $header);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $this;
    }

    public function deleteMessageByUID(int $uid): self
    {
        $result = @imap_delete($this->config['connect'], $uid, FT_UID);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $this;
    }

    public function deleteMessageByMsgNo(int $messageNumber): self
    {
        return $this->deleteMessageByUID($this->getUIDByMsgNo($messageNumber));
    }

    public function expunge(): self
    {
        $result = @imap_expunge($this->config['connect']);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $this;
    }

    public function getPermissions(string $folder): array
    {
        $folders = $this->listFolders();
        if (!in_array($folder, $folders)) {
            throw new \RuntimeException('Unknown folder.');
        }
        $result = @imap_getacl($this->config['connect'], @imap_utf7_encode($folder));
        if ($result === false || !is_array($result)) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $result;
    }

    public function setPermissions(string $folder, string $user, string $acl): self
    {
        $acl = $acl == 'none' ? '' : $acl;
        $acl = $acl == 'read' ? 'lrs' : $acl;
        $acl = $acl == 'post' ? 'lrps' : $acl;
        $acl = $acl == 'append' ? 'lrsip' : $acl;
        $acl = $acl == 'write' ? 'lrswipcd' : $acl;
        $acl = $acl == 'all' ? 'lrswipcda' : $acl;
        $acl = $acl == 'super' ? 'lrswipkxtecda' : $acl;
        if (!preg_match('/^[lrswipkxtecda]{0,}$/', $acl)) {
            throw new \RuntimeException('Unknown permission.');
        }
        $folders = $this->listFolders();
        if (!in_array($folder, $folders)) {
            throw new \RuntimeException('Unknown folder.');
        }
        $result = @imap_setacl($this->config['connect'], @imap_utf7_encode($folder), $user, $acl);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $this;
    }

    public function addSubscribe(string $folder): self
    {
        $folders = $this->listFolders();
        if (!in_array($folder, $folders)) {
            throw new \RuntimeException('Unknown folder.');
        }
        $result = @imap_subscribe($this->config['connect'], $this->config['link'] . $folder);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $this;
    }

    public function listSubscribed(): array
    {
        $result = @imap_lsub($this->config['connect'], $this->config['link'], '*');
        if (!is_array($result)) {
            throw new \RuntimeException(@imap_last_error());
        }
        foreach ($result as $intKey => $folder) {
            $result[$intKey] = preg_replace('/^\{.*\}(.*)$/', '\\1', $folder);
        }
        return $result;
    }

    public function removeSubscribe(string $folder): self
    {
        $folders = $this->listFolders();
        if (!in_array($folder, $folders)) {
            throw new \RuntimeException('Unknown folder');
        }
        $result = @imap_unsubscribe($this->config['connect'], $this->config['link'] . $folder);
        if ($result === false) {
            throw new \RuntimeException(@imap_last_error());
        }
        return $this;
    }

    private function decodeString(string $text): string
    {
        $text = str_replace("\t", ' ', $text);
        $text = trim($text);
        $words = explode(' ', $text);

        foreach ($words as $index => $word) {
            if (trim($words[$index]) == '') {
                unset($words[$index]);
                continue;
            }
            preg_match('/=\?(.*)\?([a-zA-Z0-9])\?(.*)\?=/Ue', $word, $chars);
            if (count($chars) == 0) {
                $words[$index] = trim($words[$index]) . ' ';
                continue;
            }
            $chars[1] = strtolower($chars[1]);
            $chars[2] = strtolower($chars[2]);
            if ($chars[2] == 'b') {
                $chars[3] = base64_decode($chars[3]);
                $words[$index] = $chars[3];
            } else {
                if ($chars[2] == 'q') {
                    $chars[3] = str_replace('_', ' ', $chars[3]);
                    $chars[3] = urldecode(str_replace('=', '%', $chars[3]));
                    $words[$index] = $chars[3];
                }
            }
            if ($chars[1] != 'utf-8') {
                $tmp = iconv($chars[1], 'utf-8', $words[$index]);
                if (trim($tmp) != '') {
                    $words[$index] = $tmp;
                }
            }
            $words[$index] = trim($words[$index]);
        }
        $text = implode(' ', $words);
        $text = preg_replace('/\s{1,}/', ' ', $text);
        $text = str_replace("\r", '', $text);
        $text = str_replace("\n", '', $text);
        return trim($text);
    }

    public function encodeString(string $text): string
    {
        return '=?utf-8?B?' . base64_encode($text) . '?=';
    }

}
