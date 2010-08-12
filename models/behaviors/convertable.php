<?php
class ConvertableBehavior extends ModelBehavior {

    var $__settings = array();

    function setup(&$model, $config = array()) {
        $this->__settings[$model->alias] = (array)$config;
    }

    function beforeFind(&$model, $queryData) {
        if (isset($queryData['conditions'])) {
            $conditions = (array)$queryData['conditions'];
            foreach ($conditions as $field => &$value) {
                $field = explode(' ', $field);
                $field = explode('.', $field[0]);
                if (count($field) === 1) {
                    array_unshift($field, $model->alias);
                } elseif ($field[0] !== $model->alias) {
                    continue;
                }
                if (isset($this->__settings[$model->alias][$field[1]]['beforeSave'])) {
                    $value = $this->_triggerCallback($model, $this->__settings[$model->alias][$field[1]]['beforeSave'], $value);
                }
            }
            $queryData['conditions'] = $conditions;
        }
        return $queryData;
    }

    function beforeSave(&$model) {
        $data = array_intersect_key($model->data[$model->alias], $this->__settings[$model->alias]);
        foreach ($data as $field => &$value) {
            if (isset($this->__settings[$model->alias][$field]['beforeSave'])) {
                $value = $this->_triggerCallback($model, $this->__settings[$model->alias][$field]['beforeSave'], $value);
            }
        }
        $model->data[$model->alias] = array_merge($model->data[$model->alias], $data);
        return true;
    }

    function afterFind(&$model, $results) {
        foreach ($results as &$result) {
            if (isset($result[$model->alias])) {
                foreach ($result[$model->alias] as $field => &$value) {
                    if (isset($this->__settings[$model->alias][$field]['afterFind'])) {
                        $value = $this->_triggerCallback($model, $this->__settings[$model->alias][$field]['afterFind'], $value);
                    }
                }
            }
        }
        return $results;
    }

    function afterSave(&$model) {
        $data = array_intersect_key($model->data[$model->alias], $this->__settings[$model->alias]);
        foreach ($data as $field => &$value) {
            if (isset($this->__settings[$model->alias][$field]['afterFind'])) {
                $value = $this->_triggerCallback($model, $this->__settings[$model->alias][$field]['afterFind'], $value);
            }
        }
        $model->data[$model->alias] = array_merge($model->data[$model->alias], $data);
        return true;
    }

    function _triggerCallback(&$model, $callback, $value) {
        if (method_exists($model, $callback)) {
            $value = $model->{$this->__settings[$model->alias][$field]['beforeSave']}($value);
        } elseif (method_exists($this, $callback)) {
            $value = $this->{$callback}($model, $value);
        } elseif (function_exists($callback)) {
            $value = $callback($value);
        }
        return $value;
    }

    function ipToLong(&$model, $ip) {
        if (!preg_match('#^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$#', $ip, $_ip)) {
            return $ip;
        }
        for ($i = 1; $i < 5; $i++) {
            if ($_ip[$i] < 0 || $_ip[$i] > 255) {
                return $ip;
            }
        }
        return (
            ($_ip[1] * pow(256, 3)) +
            ($_ip[2] * pow(256, 2)) +
            ($_ip[3] * 256) +
            ($_ip[4])
        );
    }

    function longToIp(&$model, $long) {
        if (!is_numeric($long) || 0 > $long || 4294967295 < $long) {
            return $long;
        }
        $ip = array();
        $ipt = $long % 256;
        $long -= $ipt;
        $long /= 256;
        array_unshift($ip, $ipt);
        $ipt = $long % 256;
        $long -= $ipt;
        $long /= 256;
        array_unshift($ip, $ipt);
        $ipt = $long % 256;
        $long -= $ipt;
        $long /= 256;
        array_unshift($ip, $ipt);
        $ipt = $long % 256;
        array_unshift($ip, $ipt);
        return implode('.', $ip);
    }
}
