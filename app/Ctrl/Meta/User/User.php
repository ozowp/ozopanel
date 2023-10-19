<?php 
namespace WAM\Ctrl\Meta\User;

class User
{

    public function __construct()
    {
        add_action('show_user_profile', [$this, 'user_profile_fields']);
        add_action('edit_user_profile', [$this, 'user_profile_fields']);

        add_action('personal_options_update', [$this, 'profile_update_action']);
        add_action('edit_user_profile_update', [$this, 'profile_update_action']);
    }

    function user_profile_fields($user) {  
        ?> 
    <?php  }

    function profile_update_action($user_id)
    { 
    }
}
