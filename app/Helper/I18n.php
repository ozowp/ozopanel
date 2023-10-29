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
            'select' => esc_html__('Select', 'ozopanel'),
            'backTo' => esc_html__('Back to', 'ozopanel'),
            'del' => esc_html__('Delete', 'ozopanel'),
            'menu_select_guide' => esc_html__('Select menu and submenu which you want to allow for this', 'ozopanel'),
            'submit' => esc_html__('Submit', 'ozopanel'),
            'submiting' => esc_html__('Submitting...', 'ozopanel'),
            'update' => esc_html__('Update', 'ozopanel'),
            'updating' => esc_html__('Updatting...', 'ozopanel'),
            'restrict' => esc_html__('Restrict', 'ozopanel'),
            'restriction' => esc_html__('Restriction', 'ozopanel'),
            'admin_columns' => esc_html__('Admin Columns', 'ozopanel'),
            //table
            'id' => esc_html__('ID', 'ozopanel'),
            'name' => esc_html__('Name', 'ozopanel'),
            'email' => esc_html__('Email', 'ozopanel'),
            'label' => esc_html__('Label', 'ozopanel'),
            'edit' => esc_html__('Edit', 'ozopanel'),
            'actions' => esc_html__('Actions', 'ozopanel'),
            // user
            'user' => esc_html__('User', 'ozopanel'),
            'users' => esc_html__('Users', 'ozopanel'),
            // role
            'role' => esc_html__('Role', 'ozopanel'),
            'roles' => esc_html__('Roles', 'ozopanel'),
            // settings
            'settings' => esc_html__('Settings', 'ozopanel'),
            // settings > tabs
            'general' => esc_html__('General', 'ozopanel'),
            'other' => esc_html__('Other', 'ozopanel'),
            // toast
            'pls_select_menu' => esc_html__('Please select Menu', 'ozopanel'),
            'pls_select_user' => esc_html__('Please Select User', 'ozopanel'),
            'pls_select_role' => esc_html__('Please Seleact Role', 'ozopanel'),
            'sucAdd' => esc_html__('Successfully Added', 'ozopanel'),
            'sucEdit' => esc_html__('Successfully Updated', 'ozopanel'),
            'sucDel' => esc_html__('Successfully Deleted', 'ozopanel')
        ];
    }
}