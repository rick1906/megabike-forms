<?php

namespace megabike\forms;

abstract class BaseDbFormTable extends AbstractDbFormTable
{

    protected $isFilterDisplayed = true;
    protected $isStatsMessageDisplayed = true;
    protected $isOnPageControlDisplayed = true;
    protected $onPageVariants = [10, 20, 50, 100, 200, 500, 1000];

    /**
     * 
     * @return array
     */
    public final function getOnPageVariants()
    {
        return $this->onPageVariants;
    }

    /**
     * 
     * @param boolean $array
     */
    public final function setOnPageVariants($array)
    {
        $this->onPageVariants = (array)$array;
    }

    /**
     * 
     * @return boolean
     */
    public final function isFilterDisplayed()
    {
        return $this->isFilterDisplayed;
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsFilterDisplayed($flag)
    {
        $this->isFilterDisplayed = (bool)$flag;
    }

    /**
     * 
     * @return boolean
     */
    public final function isStatsMessageDisplayed()
    {
        return $this->isStatsMessageDisplayed;
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsStatsMessageDisplayed($flag)
    {
        $this->isStatsMessageDisplayed = (bool)$flag;
    }

    /**
     * 
     * @return boolean
     */
    public final function isOnPageControlDisplayed()
    {
        return $this->isOnPageControlDisplayed;
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsOnPageControlDisplayed($flag)
    {
        $this->isOnPageControlDisplayed = (bool)$flag;
    }

    /**
     * 
     * @return string
     */
    public function getBlockHtmlId()
    {
        return $this->getHtmlIdPrefix().'_'.$this->getTableAlias();
    }

    /**
     * 
     * @return string
     */
    public function getTableHtmlId()
    {
        return $this->getHtmlIdPrefix().'_'.$this->getTableAlias().'_table';
    }

    /**
     * 
     * @return string
     */
    public function getHtmlIdPrefix()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * 
     * @return string
     */
    protected function getCssClassPrefix()
    {
        return 'forms';
    }

    /**
     * 
     * @param FieldInterface $field
     * @return boolean
     */
    protected function isFieldDisplayed($field)
    {
        return $field->isShow();
    }

    public function render()
    {
        $buffer = '';
        $buffer .= '<div class="'.$this->getCssClassPrefix().'-table" id="'.$this->getBlockHtmlId().'">';
        if ($this->isFilterDisplayed && $this->isFilterEnabled()) {
            $buffer .= $this->renderFilter();
        }

        $total = $this->getTotalRecords();
        if ($total <= 0 || $this->isStatsMessageDisplayed()) {
            $buffer .= $this->renderStats();
        }
        if ($total > 0) {
            $buffer .= $this->renderTable();
        }

        if ($this->isNavigationEnabled() && $this->getOnPage()) {
            $buffer .= $this->renderNavigation();
        }

        $buffer .= '</div>';
        return $buffer;
    }

    protected function generateStatsMessage($total, $min, $max)
    {
        if ($total > 0) {
            return "Displaying records from {$min} to {$max}, {$total} in total.";
        } else {
            return "No records.";
        }
    }

    public function renderStats()
    {
        $onPage = $this->getOnPage();
        $total = $this->getTotalRecords();
        $start = $this->getStartIndex();
        $min = $start + 1;
        $max = $onPage ? min($start + $onPage, $total) : $total;

        $buffer = '';
        $buffer .= '<div class="'.$this->getCssClassPrefix().'-table-stats">';
        $buffer .= '<span class="'.$this->getCssClassPrefix().'-table-stats-text">';
        $buffer .= $this->generateStatsMessage($total, $min, $max);
        $buffer .= '</span>';
        if ($this->isOnPageControlDisplayed && $this->isNavigationEnabled()) {
            $buffer .= $this->renderOnPageControl();
        }
        $buffer .= '</div>';
        return $buffer;
    }

    protected function generateFilterHeaderText()
    {
        return 'Filter';
    }

    protected function generateFilterApplyText()
    {
        return 'Apply filter';
    }

    protected function generateFilterResetText()
    {
        return 'Reset filter';
    }

    public function processFilterForm($input)
    {
        if (isset($input['_filter_reset'])) {
            $this->setWhereFilter(array());
            $this->setPage(1);
            return true;
        }
        if (!isset($input['_filter_apply'])) {
            return false;
        }

        unset($input['_filter_reset']);
        unset($input['_filter_apply']);
        $this->filterManager->setValues($input);
        $where = $this->filterManager->getWhere();
        $this->setWhereFilter($where);
        $this->setPage(1);
        return true;
    }

    public function renderFilter()
    {
        $fields = $this->filterManager->generateFilterFields();
        if ($fields) {
            $class = $this->getCssClassPrefix().'-filter';
            if ($this->filterManager->isActive()) {
                $class .= ' '.$this->getCssClassPrefix().'-filter-active';
            }
            $buffer = '';
            $buffer .= '<div class="'.$class.'">';
            $buffer .= '<div class="h"><span>'.$this->generateFilterHeaderText().'</span></div>';
            $buffer .= '<form action="" method="post">';
            foreach ($fields as $field) {
                $buffer .= $this->renderFilterField($field);
            }
            $buffer .= '<div class="'.$this->getCssClassPrefix().'-filter-buttons">';
            $buffer .= '<span><input type="submit" name="_filter_apply" value="'.html_encode($this->generateFilterApplyText()).'" /></span>';
            $buffer .= '<span><input type="submit" name="_filter_reset" value="'.html_encode($this->generateFilterResetText()).'" /></span>';
            $buffer .= '</div>';
            $buffer .= '</form>';
            $buffer .= '</div>';
            return $buffer;
        } else {
            return '';
        }
    }

    protected function renderFilterField($field)
    {
        $cssClass = $this->getCssClassPrefix().'-filter-item';
        $fieldCssClass = $field->getAttribute('cssClass');
        if ($fieldCssClass) {
            $cssClass .= ' '.$fieldCssClass;
        }
        $buffer = '';
        $buffer .= '<div class="'.$cssClass.'">';
        $buffer .= '<div class="nm">'.html_encode($field->getName()).'</div>';
        $buffer .= '<div class="vl">'.$this->renderFilterFieldValue($field).'</div>';
        $buffer .= '</div>';
        return $buffer;
    }

    protected function renderFilterFieldValue($field)
    {
        return $field->generateInputHtml($this->filterManager, $this->filterManager->getValues(), null);
    }

    public function renderTable()
    {
        $buffer = '';
        $buffer .= '<div class="'.$this->getCssClassPrefix().'-table-container">';
        $buffer .= $this->renderTableHeader();
        $buffer .= $this->renderTableContent($this->getOnPage());
        $buffer .= $this->renderTableFooter();
        $buffer .= '</div>';
        return $buffer;
    }

    protected function renderTableHeader()
    {
        $buffer = '';
        $buffer .= '<table class="'.$this->getCssClassPrefix().'-table-table" id="'.$this->getTableHtmlId().'">';
        $buffer .= '<thead>';
        $buffer .= '<tr class="'.$this->getCssClassPrefix().'-table-captions">';
        $buffer .= $this->renderCaptions();
        if ($this->isControlEnabled) {
            $buffer .= $this->renderButtonsCaptions();
        }
        $buffer .= '</tr>';
        $buffer .= '</thead>';
        return $buffer;
    }

    protected function renderButtonsCaptions()
    {
        $buttons = $this->getButtons();
        if ($buttons) {
            return '<th></th>';
        } else {
            return '';
        }
    }

    protected function renderTableFooter()
    {
        return '</table>';
    }

    protected function renderCaptions()
    {
        $buffer = '';
        foreach ($this->getFields() as $field) {
            if ($this->isFieldDisplayed($field)) {
                $buffer .= $this->renderFieldCaptionBlock($field);
            }
        }
        return $buffer;
    }

    protected function generateOrderSwitchNote()
    {
        return 'Order';
    }

    protected function generateOrderSwitchTypeText($order)
    {
        return (string)$order === 'desc' ? 'Descending' : 'Ascending';
    }

    protected function renderOrderSwitchSymbol($order)
    {
        return (string)$order === 'desc' ? '&#9660;' : '&#9650;';
    }

    protected function renderOrderSwitch($field, $order)
    {
        $orderNote = $this->generateOrderSwitchNote();
        $orderUrl = $this->generateOrderSwitchUrl($this->getBaseUrl(), $field->getId());
        $buffer = '';
        if ($order) {
            $buffer .= '<table width="100%" class="cap-order"><tr>';
            $buffer .= '<td class="ord-sym">';
            $buffer .= '<span title="'.html_encode($this->generateOrderSwitchTypeText($order)).'">';
            $buffer .= $this->renderOrderSwitchSymbol($order);
            $buffer .= '</span>';
            $buffer .= '</td>';
            $buffer .= '<td class="cap">';
            $buffer .= '<a href="'.html_encode($orderUrl).'" title="'.html_encode($orderNote).'">';
            $buffer .= $this->renderFieldCaption($field);
            $buffer .= '</a>';
            $buffer .= '</td>';
            $buffer .= '<td class="ord-sym"></td>';
            $buffer .= '</tr></table>';
        } else {
            $buffer .= '<div class="cap">';
            $buffer .= '<a href="'.html_encode($orderUrl).'" title="'.html_encode($orderNote).'">';
            $buffer .= $this->renderFieldCaption($field);
            $buffer .= '</a>';
            $buffer .= '</div>';
        }
        return $buffer;
    }

    protected function renderFieldCaptionBlock($field)
    {
        $buffer = '';
        $buffer .= '<th>';
        if ($this->isSortingEnabled() && $field->isOrderField()) {
            $status = $this->criteriaManager->getFieldOrderStatus($field->getId());
            $buffer .= $this->renderOrderSwitch($field, $status);
        } else {
            $buffer .= '<div class="cap">';
            $buffer .= $this->renderFieldCaption($field);
            $buffer .= '</div>';
        }
        $buffer .= '</th>';
        return $buffer;
    }

    protected function renderFieldCaption($field)
    {
        $name = $field->getName();
        $shortName = $field->getShortName();
        if ($name !== $shortName) {
            return '<span title="'.html_encode($name).'">'.html_encode($shortName).'</span>';
        } else {
            return html_encode($name);
        }
    }

    protected function renderTableContent($limit = null)
    {
        $index = 0;
        $buffer = '';
        while (($data = $this->getNextItem()) !== null) {
            $buffer .= $this->renderItem($data, $index);
            $index++;
            if ($limit && $index >= $limit) {
                break;
            }
        }
        //TODO: free mysql result (changes to dbc & dataReader)
        return $buffer;
    }

    protected function renderItem($data, $index)
    {
        $buffer = '';
        $buffer .= '<tr class="'.($index % 2 ? 'odd' : 'even').'">';
        $buffer .= $this->renderItemValues($data);
        if ($this->isControlEnabled()) {
            $buffer .= $this->renderButtons($data);
        }
        $buffer .= '</tr>';
        return $buffer;
    }

    protected function renderButtons($data)
    {
        $buttons = $this->getButtons();
        if ($buttons) {
            $buffer = '';
            $buffer .= '<td class="'.$this->getCssClassPrefix().'-table-buttons" width="1">';
            $buffer .= '<table><tr>';
            foreach ($buttons as $button) {
                $buffer .= '<td class="'.$this->getCssClassPrefix().'-button">'.$this->renderButton($button, $data).'</td>';
            }
            $buffer .= '</tr></table>';
            $buffer .= '</td>';
            return $buffer;
        } else {
            return '';
        }
    }

    protected function renderItemValues($data)
    {
        $buffer = '';
        foreach ($this->getFields() as $field) {
            $buffer .= $this->renderField($field, $data);
        }
        return $buffer;
    }

    protected function renderField($field, $data)
    {
        if ($field->isShow()) {
            return '<td>'.$this->renderFieldValue($field, $data).'</td>';
        } else {
            return '';
        }
    }

    protected function isButtonEnabled($button, $data)
    {
        return $button->isEnabledFor($this->extractDataForField($button, $data));
    }

    protected function renderButton($button, $data)
    {
        if ($button->isShow() && $this->isButtonEnabled($button, $data)) {
            return $button->getHtmlValue($this->extractDataForField($button, $data));
        } else {
            return '';
        }
    }

    protected function renderFieldValue($field, $data)
    {
        return $field->getHtmlValue($this->extractDataForField($field, $data));
    }

    public function renderNavigation()
    {
        if (!$this->isNavigationEnabled() || !$this->getOnPage()) {
            return '';
        }

        $total = $this->getTotalRecords();
        if (!$total) {
            return '';
        }

        $buffer = '';
        $navigation = $this->generateNavigation('');
        if ($navigation) {
            $buffer .= '<div class="'.$this->getCssClassPrefix().'-table-navigation">';
            $buffer .= '<div class="'.$this->getCssClassPrefix().'-table-pages">';
            foreach ($navigation as $nav) {
                $cssClass = $nav['type'];
                if (!empty($nav['active'])) {
                    $cssClass .= ' act';
                }
                $buffer .= '<span class="'.$cssClass.'">';
                if (!empty($nav['url'])) {
                    $buffer .= '<a href="'.html_encode($nav['url']).'">'.$nav['content'].'</a>';
                } else {
                    $buffer .= $nav['content'];
                }
                $buffer .= '</span>';
            }
            $buffer .= '</div>';
            $buffer .= '</div>';
        }
        return $buffer;
    }

    protected function generateOnPageControlText()
    {
        return 'On page:';
    }

    protected function generateOnPageAllText()
    {
        return 'all';
    }

    protected function renderOnPageControl()
    {
        $onPage = (int)$this->getOnPage();
        $variants = (array)$this->onPageVariants;
        $variants[] = 0;
        $variants[] = $onPage;
        $items = array_merge(array_unique($variants));
        sort($items);
        $js = "if(this.selectedIndex>=0){location.href=this.options[this.selectedIndex].getAttribute('data-url');}";
        $buffer = '';
        $buffer .= '<div class="'.$this->getCssClassPrefix().'-table-onpage">';
        $buffer .= '<span>'.$this->generateOnPageControlText().'</span>';
        $buffer .= '<select name="__onpage" onchange="'.html_encode($js).'" autocomplete="off">';
        foreach ($items as $item) {
            $url = $this->generateUrl('', true, ['onpage' => (int)$item]);
            $active = (int)$item === $onPage;
            $text = $item ? $item : $this->generateOnPageAllText();
            $buffer .= '<option value="'.html_encode($item).'"';
            $buffer .= ' data-url="'.html_encode($url).'"';
            if ($active) {
                $buffer .= ' selected';
            }
            $buffer .= '>';
            $buffer .= html_encode($text);
            $buffer .= '</option>';
        }
        $buffer .= '</select>';
        $buffer .= '</div>';
        return " {$buffer}";
    }

}
