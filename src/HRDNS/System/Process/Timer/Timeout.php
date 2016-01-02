<?php

namespace HRDNS\System\Process\Timer;

use HRDNS\Types\Struct;

class Timeout extends Struct
{

    protected $data = array(
        'id' => null,
        'run' => 0,
        'func' => null,
    );

    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->data['id'] = spl_object_hash($this);
    }

    protected function __clone()
    {
        $this->data['id'] = spl_object_hash($this);
    }

}
