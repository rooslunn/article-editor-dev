<?php


namespace Dreamscape\Foundation;


final class ACL
{
    const ARTICLE_TOOL_ROLES = ['ARTICLE_TOOL_PUBLISHER_ROLE', 'ARTICLE_TOOL_EDITOR_ROLE'];

    public static function roles()
    {
        $result = [];
        foreach (self::ARTICLE_TOOL_ROLES as $role) {
            $result[$role] = \crms_user::check_current_permissions($role);
        }
        return $result;
    }

    public static function authUser()
    {
        foreach (self::ARTICLE_TOOL_ROLES as $role) {
            if (! \crms_user::check_current_permissions($role) ) {
                return false;
            }
        }
        return true;
    }

}