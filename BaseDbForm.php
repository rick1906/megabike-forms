<?php

namespace megabike\forms;

abstract class BaseDbForm extends AbstractDbForm
{

    protected $backUrl = null;
    protected $formActionUrl = '';
    protected $formMethod = 'post';
    protected $onSubmitJs = '';
    protected $isFormEnabled = true;
    protected $isFormReadOnly = false;
    protected $isActionDisabled = false;
    protected $customHeaderText = '';
    protected $customActionText = '';
    protected $actionNoteHtml = '';

    /**
     * 
     * @return string
     */
    public final function getFormActionUrl()
    {
        return $this->formActionUrl;
    }

    /**
     * 
     * @param string $url
     */
    public final function setFormActionUrl($url)
    {
        $this->formActionUrl = (string)$url;
    }

    /**
     * 
     * @return string
     */
    public final function getFormOnSubmitJs()
    {
        return $this->onSubmitJs;
    }

    /**
     * 
     * @param string $js
     */
    public final function setFormOnSubmitJs($js)
    {
        $this->onSubmitJs = (string)$js;
    }

    /**
     * 
     * @return string
     */
    public final function getFormMethod()
    {
        return $this->formMethod;
    }

    /**
     * 
     * @param string $method
     */
    public final function setFormMethod($method)
    {
        $this->formMethod = (string)$method;
    }

    /**
     * 
     * @return boolean
     */
    public final function isFormEnabled()
    {
        return $this->isFormEnabled;
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsFormEnabled($flag)
    {
        $this->isFormEnabled = (string)$flag;
    }

    /**
     * 
     * @return boolean
     */
    public final function isFormReadOnly()
    {
        return $this->isFormReadOnly;
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsFormReadOnly($flag)
    {
        $this->isFormReadOnly = (bool)$flag;
    }

    /**
     * 
     * @return boolean
     */
    public final function isActionDisabled()
    {
        return $this->isActionDisabled;
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsActionDisabled($flag)
    {
        $this->isActionDisabled = (bool)$flag;
    }

    /**
     * 
     * @return string
     */
    public final function getCustomHeaderText()
    {
        return $this->customHeaderText;
    }

    /**
     * 
     * @param string $string
     */
    public final function setCustomHeaderText($string)
    {
        $this->customHeaderText = (string)$string;
    }

    /**
     * 
     * @return string
     */
    public final function getCustomActionText()
    {
        return $this->customActionText;
    }

    /**
     * 
     * @param string $string
     */
    public final function setCustomActionText($string)
    {
        $this->customActionText = (string)$string;
    }

    /**
     * 
     * @return string
     */
    public final function getActionNoteHtml()
    {
        return $this->actionNoteHtml;
    }

    /**
     * 
     * @param string $string
     */
    public final function setActionNoteHtml($string)
    {
        $this->actionNoteHtml = (string)$string;
    }

    /**
     * 
     * @param string $string
     */
    public final function setActionNoteText($string)
    {
        $this->actionNoteHtml = html_encode($string);
    }

    /**
     * 
     * @param string $string
     */
    public final function setActionNoteError($string)
    {
        $this->actionNoteHtml = '<p class="e">'.html_encode($string)."</p>";
    }

    /**
     * 
     * @return string
     */
    public function getBackUrl()
    {
        return (string)$this->backUrl;
    }

    /**
     * 
     * @param string $url
     */
    public final function setBackUrl($url)
    {
        $this->backUrl = $url !== null ? (string)$url : null;
    }

    /**
     * 
     * @return string
     */
    public function getBlockHtmlId()
    {
        return $this->getHtmlIdPrefix().'_'.$this->getTableAlias().$this->dataId;
    }

    /**
     * 
     * @return string
     */
    public function getFormHtmlId()
    {
        return $this->getHtmlIdPrefix().'_'.$this->getTableAlias().$this->dataId.'_form';
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
        if ($this->isFormEnabled) {
            if (!$this->isFieldReadOnly($field) && !$this->isFormReadOnly) {
                return $this->isFormFieldDisplayed($field);
            } else {
                return $this->isReadOnlyFieldDisplayed($field);
            }
        } else {
            return $this->isReadOnlyFieldDisplayed($field);
        }
    }

    /**
     * 
     * @param FieldInterface $field
     * @return boolean
     */
    protected function isFormFieldDisplayed($field)
    {
        return $this->isFormEnabled;
    }

    /**
     * 
     * @param FieldInterface $field
     * @return boolean
     */
    protected function isReadOnlyFieldDisplayed($field)
    {
        if ($this->scenario === self::SCENARIO_ADD) {
            return false;
        } elseif ($this->isFormEnabled) {
            return $field->isShowInForm();
        } else {
            return $field->isShowFull();
        }
    }

    protected function processDefaultScenario()
    {
        if ($this->isFormReadOnly) {
            return array();
        } else {
            return parent::processDefaultScenario();
        }
    }

    public function render()
    {
        $buffer = '';
        $buffer .= '<div class="'.$this->getCssClassPrefix().'-form" id="'.$this->getBlockHtmlId().'">';
        $buffer .= $this->renderContent();
        $buffer .= '</div>';
        return $buffer;
    }

    public function renderContent()
    {
        $buffer = '';
        $buffer .= '<div class="'.$this->getCssClassPrefix().'-form-content">';
        if ($this->isFormEnabled) {
            $buffer .= '<form enctype="multipart/form-data" method="'.html_encode($this->formMethod).'" action="'.html_encode($this->formActionUrl).'"';
            $buffer .= ' id="'.html_encode($this->getFormHtmlId()).'" onsubmit="'.html_encode($this->onSubmitJs).'">';
            $buffer .= $this->renderFormContent();
            $buffer .= '</form>';
        } else {
            $buffer .= '<div class="'.$this->getCssClassPrefix().'-form-view">';
            $buffer .= $this->renderFormContent();
            $buffer .= '</div>';
        }
        $buffer .= '</div>';
        return $buffer;
    }

    protected function renderFormContent()
    {
        $buffer = '';
        $buffer .= $this->renderFormHeader();
        $buffer .= $this->renderFormTable();
        $buffer .= $this->renderFormFooter();
        return $buffer;
    }

    protected function renderFormHeader()
    {
        $buffer = '';
        $buffer .= $this->renderMessages();
        return $buffer;
    }

    protected function renderMessages()
    {
        $buffer = '';
        $errors = $this->getErrors();
        if ($errors) {
            $buffer .= '<div class="'.$this->getCssClassPrefix().'-form-errors">';
            $buffer .= $this->renderErrors($errors);
            $buffer .= '</div>';
        }
        return $buffer;
    }

    protected function generateErrorsPrefixMessage()
    {
        return 'Form processed with errors:';
    }

    protected function renderErrors($errors)
    {
        $buffer = '';
        $buffer .= '<p class="h">'.$this->generateErrorsPrefixMessage().'</p>';
        foreach ($errors as $error) {
            $errorHtml = empty($error['raw']) ? $error['message'] : html_encode($error['message']);
            if (!empty($error['fieldId'])) {
                $field = $this->getField($error['fieldId']);
                if ($field) {
                    $buffer .= '<p class="e ef"><i>'.html_encode($field->getName())."</i>: ".$errorHtml.'</p>';
                } else {
                    $buffer .= '<p class="e ef"><i>'.html_encode($error['fieldId'])."</i>: ".$errorHtml.'</p>';
                }
            } else {
                $buffer .= '<p class="e">'.$errorHtml.'</p>';
            }
        }
        return $buffer;
    }

    protected function renderFormFooter()
    {
        return '';
    }

    protected function renderFormTableHeader()
    {
        $buffer = '';
        $buffer .= '<tr>';
        $buffer .= '<th colspan="2">';
        $buffer .= $this->renderHeaderText();
        $buffer .= '</th>';
        $buffer .= '</tr>';
        return $buffer;
    }

    public function getFormHeader()
    {
        $name = $this->dataId ? (string)$this->getCurrentName() : '';
        $headerTextPattern = (string)$this->customHeaderText !== '' ? $this->customHeaderText : $this->generateHeaderText($this->scenario);
        $headerText = $name !== '' ? sprintf($headerTextPattern, '&laquo;'.html_encode($name).'&raquo;') : trim(sprintf($headerTextPattern, ''));
        return $headerText;
    }

    public function getFormTitle()
    {
        $header = strip_tags($this->getFormHeader());
        return html_entity_decode($header, ENT_COMPAT, FormSettings::getInstance()->getCharset());
    }

    protected function generateHeaderText($scenario)
    {
        return 'Item %s';
    }

    protected function renderHeaderText()
    {
        return $this->getFormHeader();
    }

    protected function generateBackText()
    {
        return 'Return';
    }

    protected function renderBackLink()
    {
        $url = $this->getBackUrl();
        if ((string)$url !== '') {
            return '<a class="back" href="'.html_encode($url).'">'.html_encode($this->generateBackText()).'</a>';
        } else {
            return '';
        }
    }

    protected function getDataIdString()
    {
        return (string)$this->dataId;
    }

    protected function renderButtonsBlock()
    {
        $buffer = '';
        $buffer .= '<input type="hidden" name="scenario" value="'.html_encode($this->scenario).'" />';
        if ($this->dataId !== null) {
            $buffer .= '<input type="hidden" name="id" value="'.html_encode($this->getDataIdString()).'" />';
        }
        if ((string)$this->actionNoteHtml !== null) {
            $buffer .= '<div class="'.$this->getCssClassPrefix().'-form-action-note">';
            $buffer .= $this->actionNoteHtml;
            $buffer .= '</div>';
        }
        if (!$this->isActionDisabled) {
            $buffer .= $this->renderButtons();
        }
        return $buffer;
    }

    protected function generateButtonText($scenario)
    {
        return 'Apply';
    }

    protected function renderButtons()
    {
        $buttonText = (string)$this->customActionText !== '' ? $this->customActionText : $this->generateButtonText($this->scenario);
        return '<input type="submit" value="'.html_encode($buttonText).'" class="btn" />';
    }

    protected function renderFormTableFooter()
    {
        $buffer = '';
        $buffer .= '<tr class="'.$this->getCssClassPrefix().'-form-footer">';
        $buffer .= '<td class="'.$this->getCssClassPrefix().'-form-back-cell">';
        $buffer .= $this->renderBackLink();
        $buffer .= '</td>';
        $buffer .= '<td class="'.$this->getCssClassPrefix().'-form-buttons-cell">';
        if ($this->isFormEnabled) {
            $buffer .= $this->renderButtonsBlock();
        }
        $buffer .= '</td>';
        $buffer .= '</tr>';
        return $buffer;
    }

    protected function renderFormTable()
    {
        $buffer = '';
        $buffer .= '<table class="'.$this->getCssClassPrefix().'-form-table">';
        $buffer .= $this->renderFormTableHeader();
        $buffer .= $this->renderFormFields();
        $buffer .= $this->renderFormTableFooter();
        $buffer .= '</table>';
        return $buffer;
    }

    protected function getDisplayedFields()
    {
        $result = array();
        $fields = $this->getFields();
        foreach ($fields as $id => $field) {
            if ($this->isFieldDisplayed($field)) {
                $result[$id] = $field;
            }
        }
        return $result;
    }

    protected function renderFormFields()
    {
        $fields = $this->getDisplayedFields();
        $index = 0;
        $buffer = '';
        foreach ($fields as $field) {
            $content = (string)$this->renderFieldBlock($field, $index);
            if ($content !== '') {
                $index++;
                $buffer .= $content;
            }
        }
        return $buffer;
    }

    protected function renderFieldBlock($field, $index = 0)
    {
        $buffer = '';
        $buffer .= '<tr class="'.($index % 2 ? 'odd' : 'even').'">';
        $buffer .= $this->renderFieldNameCell($field);
        $buffer .= $this->renderFieldValueCell($field);
        $buffer .= '</tr>';
        return $buffer;
    }

    protected function renderFieldNameCell($field)
    {
        $buffer = '';
        $buffer .= '<td class="nm">';
        $buffer .= $this->renderFieldName($field);
        $buffer .= '</td>';
        return $buffer;
    }

    protected function renderFieldName($field)
    {
        $buffer = '';
        $buffer .= html_encode($field->getName());
        if (!$this->isFormReadOnly && $this->isFormEnabled && !$this->isFieldReadOnly($field) && $field->isRequired()) {
            $buffer .= '<span class="req">*</span>';
        }
        $buffer .= $this->renderFieldNote($field);
        return $buffer;
    }

    protected function renderFieldNote($field)
    {
        $note = (string)$field->getNote();
        if ($note !== '') {
            $buffer = '';
            $buffer .= '<div class="hlp">';
            $buffer .= nl2br(html_encode($note));
            $buffer .= '</div>';
            return $buffer;
        } else {
            return '';
        }
    }

    protected function renderFieldValueCell($field)
    {
        $buffer = '';
        $buffer .= '<td class="vl">';
        $buffer .= $this->renderFieldValue($field);
        $buffer .= '</td>';
        return $buffer;
    }

    protected function renderFieldValue($field)
    {
        if ($this->isFormEnabled && !$this->isFormReadOnly && !$this->isFieldReadOnly($field)) {
            return $this->renderFieldInput($field);
        } else {
            return $this->renderFieldView($field);
        }
    }

    protected function renderFieldInput($field)
    {
        $data = $this->extractDataForField($field, $this->getStoredOrDefaultData());
        $input = $this->extractInputForField($field, $this->getInputData());
        return $field->generateInputHtml($this, $data, $input);
    }

    protected function renderFieldView($field)
    {
        $data = $this->extractDataForField($field, $this->getStoredOrDefaultData());
        return $field->getFullHtmlValue($data);
    }

    protected function startCurrentScenario()
    {
        if ($this->isActionDisabled || !$this->isFormEnabled) {
            return false;
        }
        return parent::startCurrentScenario();
    }

}
