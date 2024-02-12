<?php

namespace OzoPanel\Hooks\Types\Action\Types;

/**
 * Action Hook Type: NavMenu
 *
 * @since 0.1.0
 */
class NavMenu
{
    public function __construct()
    {
        add_action('wp_nav_menu_item_custom_fields', [$this, 'add_fields'], 10, 2);
        add_action('wp_update_nav_menu_item', [$this, 'save_fields'], 10, 2);
    }

    /**
     * Add custom field in nav menu
     *
     * @since 0.1.0
     */
    public function add_fields($item_id)
    {
        wp_nonce_field('ozopanel_save_nav_menu', '_ozopanel_nav_menu_nonce');

        $ozopanel_who_can_see = get_post_meta($item_id, '_ozopanel_who_can_see', true) ?? '';
        $ozopanel_who_can_see_roles = (array) get_post_meta($item_id, '_ozopanel_who_can_see_roles', true) ?? [];
?>
        <div style="clear: both;">
            <p class="field-ozopanel-who-can-see description description-wide">
                <label for="edit-menu-item-ozopanel-who-can-see-<?php echo esc_attr($item_id); ?>">
                    <?php esc_html_e('Who Can See This Menu?', 'ozopanel'); ?><br />
                    <select id="edit-menu-item-ozopanel-who-can-see-<?php echo esc_attr($item_id); ?>" class="widefat code edit-menu-item-ozopanel-who-can-see" name="menu_item_ozopanel_who_can_see[<?php echo esc_attr($item_id); ?>]">
                        <option value="" <?php selected($ozopanel_who_can_see, ''); ?>><?php esc_html_e('Everyone', 'ozopanel'); ?></option>
                        <option value="login" <?php selected($ozopanel_who_can_see, 'login'); ?>><?php esc_html_e('Logged In Users', 'ozopanel'); ?></option>
                        <option value="logout" <?php selected($ozopanel_who_can_see, 'logout'); ?>><?php esc_html_e('Logged Out Users', 'ozopanel'); ?></option>
                        <option value="roles" <?php selected($ozopanel_who_can_see, 'roles'); ?>><?php esc_html_e('Specific Roles Users', 'ozopanel'); ?></option>
                    </select>
                </label>
            </p>
            <div class="field-ozopanel-roles description description-wide" style="display: none;">
                <label for="edit-menu-item-ozopanel-roles-<?php echo esc_attr($item_id); ?>">
                    <?php esc_html_e('Chose Which Roles Can See This Menu?', 'ozopanel'); ?>
                </label>
                <div>
                    <?php
                    global $wp_roles;
                    foreach ($wp_roles->role_names as $role_id => $role_name) {
                    ?>
                        <label style="margin-right: 10px" for="edit-menu-item-ozopanel-roles-<?php echo esc_attr($item_id); ?>-item-<?php echo esc_attr($role_id); ?>">
                            <input type="checkbox" id="edit-menu-item-ozopanel-roles-<?php echo esc_attr($item_id); ?>-item-<?php echo esc_attr($role_id); ?>" name="menu_item_ozopanel_who_can_see_roles[<?php echo esc_attr($item_id); ?>][]" value="<?php echo esc_attr($role_id); ?>" <?php checked(in_array($role_id, $ozopanel_who_can_see_roles), 1); ?> />
                            <?php echo esc_html($role_name); ?>
                        </label>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
<?php
    }

    /**
     * Save custom field
     *
     * @since 0.1.0
     */
    public function save_fields($menu_id, $menu_item_db_id)
    {
        // Verify this came from our screen and with proper authorization.
        if (!isset($_POST['_ozopanel_nav_menu_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_ozopanel_nav_menu_nonce'])), 'ozopanel_save_nav_menu')) {
            return $menu_id;
        }

        if (isset($_POST['menu_item_ozopanel_who_can_see'][$menu_item_db_id])) {
            $sanitized_data = sanitize_text_field($_POST['menu_item_ozopanel_who_can_see'][$menu_item_db_id]);
            update_post_meta($menu_item_db_id, '_ozopanel_who_can_see', $sanitized_data);
        } else {
            delete_post_meta($menu_item_db_id, '_ozopanel_who_can_see');
        }

        if (isset($_POST['menu_item_ozopanel_who_can_see_roles'][$menu_item_db_id])) {
            $sanitized_data = array_map('sanitize_text_field', $_POST['menu_item_ozopanel_who_can_see_roles'][$menu_item_db_id]);
            update_post_meta($menu_item_db_id, '_ozopanel_who_can_see_roles', $sanitized_data);
        } else {
            delete_post_meta($menu_item_db_id, '_ozopanel_who_can_see_roles');
        }
    }
}
