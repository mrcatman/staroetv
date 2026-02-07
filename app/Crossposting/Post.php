<?php

namespace App\Crossposting;

class Post {

    protected $params = [
        'text' => '',
        'link' => '',
        'media' => []
    ];

    protected $media_cache = [];
    protected $fields_to_update = [];

    public function setParam(string $param, mixed $value) {
        if ($param == 'text') {
            return strip_tags($value);
        }
        $this->params[$param] = $value;
    }

    public function getParam(string $param): mixed {
        if ($param == 'link_url' && isset($this->params['link']) && is_array($this->params['link'])) {
            return $this->params['link'][0];
        }
        if ($param == 'link_text' && isset($this->params['link']) && is_array($this->params['link'])) {
            return $this->params['link'][1].PHP_EOL.$this->params['link'][0];
        }

        if (isset($this->params[$param])) {
            return $this->params[$param];
        }
        return null;
    }

    public function setFieldsToUpdate($fields = []) {
        $this->fields_to_update = $fields;
    }

    public function needUpdateField($field) {
        if (isset($this->fields_to_update[$field])) {
            return $this->fields_to_update[$field];
        }
        return false;
    }




}
