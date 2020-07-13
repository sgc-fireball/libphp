<?php declare(strict_types=1);

namespace HRDNS\System\Process\Timer;

use HRDNS\Types\Struct;

class Timeout extends Struct
{

    /**
     * @var array
     */
    protected $data = array(
        'id' => null,
        'run' => 0,
        'func' => null,
    );

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->data['id'] = spl_object_hash($this);
    }

    protected function __clone()
    {
        $this->data['id'] = spl_object_hash($this);
    }

}
