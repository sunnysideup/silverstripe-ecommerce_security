<?php

class EcommerceSecurityMemberDecoration extends DataExtension
{
    private static $db = array(
        'IsWhitelisted' => 'Boolean',
        'IsSecurityRisk' => 'Boolean'
    );

    /**
     * Event handler called before writing to the database.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if($this->owner->IsSecurityRisk) {
            $this->owner->IsWhitelisted = false;
        }
    }
}
