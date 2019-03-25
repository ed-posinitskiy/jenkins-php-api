<?php

namespace Utarwyn\Jenkins;

class JenkinsObject
{
    public function __construct($data)
    {
        foreach (get_object_vars($this) as $variable => $value) {
            $defValue = (isset($this->$variable)) ? $this->$variable : '';

            $this->$variable = isset($data->$variable) ? $data->$variable : $defValue;
        }
    }
}
