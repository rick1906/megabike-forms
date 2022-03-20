<?php

namespace megabike\forms;

interface DbTableProviderInterface
{

    public function getTable();

    public function getTableKey();

    public function getTableAlias();
}
