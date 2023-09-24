<?php

namespace WHMCS\Module\Common\helpers;
use WHMCS\Database\Capsule;

class Utils {
    public static function getCustomField($params, $fieldNames) {

        if (!is_array($fieldNames)) {
            $fieldNames = [$fieldNames];
        }

        foreach($fieldNames as $fieldName){

            $customField = Capsule::table('tblcustomfields')
            ->where('fieldname', $fieldName)
            ->where('type', 'client')
            ->first();


            if ($customField) {
                $customFieldValue = Capsule::table('tblcustomfieldsvalues')
                    ->where('fieldId', $customField->id)
                    ->where('relid',  $params['client_id'])
                    ->first();

                if ($customFieldValue){
                    return $customFieldValue->value;
                }
            }
        }

        return '';
    }

}