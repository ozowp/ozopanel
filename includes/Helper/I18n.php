<?php
namespace OzoPanel\Helper;
/**
 * All Javascript files translate text
 *
 * @since 1.0.0
 */
class I18n
{
    static function app()
    {
        return [
            // general
            'label' => esc_html__('Label', 'ozopanel'),
            'select' => esc_html__('Select', 'ozopanel'),
            'view' => esc_html__('View', 'ozopanel'),
            'apply' => esc_html__('Apply', 'ozopanel'),
            'save' => esc_html__('Save', 'ozopanel'),
            'saveChanges' => esc_html__('Save Changes', 'ozopanel'),
            'resetChanges' => esc_html__('Reset Changes', 'ozopanel'),
            'cancel' => esc_html__('Cancel', 'ozopanel'),
            'backTo' => esc_html__('Back to', 'ozopanel'),
            'del' => esc_html__('Delete', 'ozopanel'),
            'submit' => esc_html__('Submit', 'ozopanel'),
            'submiting' => esc_html__('Submitting...', 'ozopanel'),
            'update' => esc_html__('Update', 'ozopanel'),
            'updating' => esc_html__('Updatting...', 'ozopanel'),
            // restriction
            'restrict' => esc_html__('Restrict', 'ozopanel'),
            'no_restriction_data' => esc_html__('You have not restrict any', 'ozopanel'),
            'restriction' => esc_html__('Restriction', 'ozopanel'),
            'menuSelectGuide' => esc_html__('Select menu and submenu which you want to allow for this', 'ozopanel'),
            'plsSelectMenu' => esc_html__('Please select Menu', 'ozopanel'),
            'plsSelectUser' => esc_html__('Please Select User', 'ozopanel'),
            'plsSelectRole' => esc_html__('Please Seleact Role', 'ozopanel'),
            'user' => esc_html__('User', 'ozopanel'),
            'users' => esc_html__('Users', 'ozopanel'),
            'role' => esc_html__('Role', 'ozopanel'),
            'roles' => esc_html__('Roles', 'ozopanel'),
            'name' => esc_html__('Name', 'ozopanel'),
            'email' => esc_html__('Email', 'ozopanel'),
            // admin menu editor
            'adminMenuEditor' => esc_html__('Admin Menu Editor', 'ozopanel'),
            'addNewMenu' => esc_html__('Add New Menu', 'ozopanel'),
            'editMenu' => esc_html__('Edit Menu', 'ozopanel'),
            'hide' => esc_html__('Hide', 'ozopanel'),
            // admin column editor
            'adminColumnEditor' => esc_html__('Admin Column Editor', 'ozopanel'),
            'addNewColumn' => esc_html__('Add New Column', 'ozopanel'),
            'editColumn' => esc_html__('Edit Column', 'ozopanel'),

            // table
            'id' => esc_html__('ID', 'ozopanel'),
            'edit' => esc_html__('Edit', 'ozopanel'),
            'actions' => esc_html__('Actions', 'ozopanel'),

            // settings
            'settings' => esc_html__('Settings', 'ozopanel'),
            // settings > tabs
            'general' => esc_html__('General', 'ozopanel'),
            'other' => esc_html__('Other', 'ozopanel'),
            // toast
            'sucAdd' => esc_html__('Successfully Added', 'ozopanel'),
            'sucEdit' => esc_html__('Successfully Updated', 'ozopanel'),
            'sucDel' => esc_html__('Successfully Deleted', 'ozopanel')
        ];
    }
}