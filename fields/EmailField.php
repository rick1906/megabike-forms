<?php

namespace megabike\forms\fields;

class EmailField extends StringField
{

    public function validate($formValue, &$error = null)
    {
        if (!parent::validate($formValue, $error)) {
            return false;
        }
        if (!preg_match('/^[^@]+@[^@]+$/', $formValue)) {
            $error = tl('Строка не является e-mail адресом');
            return false;
        }
        return true;
    }

}
