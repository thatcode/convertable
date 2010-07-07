<?php
App::import('Core', 'Model');

class ConvertableModel extends Model {

    var $useTable = 'convertables';

    var $actsAs = array(
        'Convertable.Convertable' => array(
            'ip' => array(
                'beforeSave' => 'ipToLong',
                'afterFind' => 'longToIp'
            )
        )
    );
}

class NonConvertableModel extends Model {

    var $alias = 'ConvertableModel';

    var $useTable = 'convertables';

}

class ConvertableTestCase extends CakeTestCase {

    var $fixtures = array('plugin.convertable.convertable');

    function startTest() {
        $this->Convertable = ClassRegistry::init('ConvertableModel');
        $this->NonConvertable = ClassRegistry::init('NonConvertableModel');
    }

    function testSaving() {

        $data = array(
            'ConvertableModel' => array(
                'ip' => '123.21.123.21'
            )
        );
        $result = $this->Convertable->save($data);
        $this->assertEqual($data, $result);

        $data['ConvertableModel']['id'] = $this->Convertable->id;
        $result = $this->NonConvertable->find('first', array(
            'conditions' => array(
                'ConvertableModel.id' => $this->Convertable->id
            )
        ));
        $this->assertNotEqual($data, $result);
        $data['ConvertableModel']['ip'] = 2065005333;
        $this->assertEqual($data, $result);

    }

    function testFinding() {

        $data = array(
            'ConvertableModel' => array(
                'ip' => '234356434'
            )
        );
        $result = $this->NonConvertable->save($data);
        $this->assertEqual($data, $result);

        $data['ConvertableModel']['id'] = $this->NonConvertable->id;
        $result = $this->Convertable->find('first', array(
            'conditions' => array(
                'ConvertableModel.id' => $this->NonConvertable->id
            )
        ));
        $this->assertNotEqual($data, $result);
        $data['ConvertableModel']['ip'] = '13.247.254.210';
        $this->assertEqual($data, $result);

    }

}
