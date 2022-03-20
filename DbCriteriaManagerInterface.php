<?php

namespace megabike\forms;

interface DbCriteriaManagerInterface extends CriteriaManagerInterface
{

    public function setQuery($select, $from, $where = null, $order = null, $groupby = null, $having = null);

    public function setDefaultOrder($order);

    public function addDefaultOrder($order);

    public function setDefaultWhere($where);

    public function addDefaultWhere($where);

    public function getCountQuery();

    public function getSingleRecordQuery($where);

    public function getQuery($withLimit = true);
}
