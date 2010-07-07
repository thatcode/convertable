<?php
class ConvertableFixture extends CakeTestFixture {

    var $name = 'Convertable';

    var $table = 'convertables';

    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'ip' => 'text'
    );

}
