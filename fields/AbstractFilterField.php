<?php

namespace megabike\forms\fields;

abstract class AbstractFilterField extends AbstractFormField
{

    public function isWhereField()
    {
        return true;
    }

    public final function isShow()
    {
        return false;
    }

    public final function isShowFull()
    {
        return false;
    }

    public final function isShowInForm()
    {
        return false;
    }

    public final function isReadOnly()
    {
        return true;
    }

}
