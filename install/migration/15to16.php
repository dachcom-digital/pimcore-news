<?php
include_once("pimcore/config/startup.php");

$db = \Pimcore\Db::get();

$entryTypes = $db->fetchAll("SELECT * FROM documents_elements WHERE name LIKE 'entryTypecontent%' AND type = 'select'");

foreach($entryTypes as $entryType) {
    $value = $entryType['data'];
    if ( @unserialize($value) === FALSE ) {
        $newValue = serialize([$value]);
        $data = [
            'type' => 'multiselect',
            'data' => $newValue,
        ];
        $where = "name='" . $entryType['name'] . "' AND documentId=" . $entryType['documentId'];

        echo "UPDATE documents_elements SET type='multiselect', data=' " . $newValue . "' WHERE " . $where . '<br>';

        $db->update('documents_elements', $data, $where);
    }
}