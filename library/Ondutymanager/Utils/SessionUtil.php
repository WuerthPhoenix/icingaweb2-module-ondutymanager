<?php

namespace Icinga\Module\Ondutymanager\Utils;

use Icinga\Module\Neteye\Utils\BaseSessionUtil;

class SessionUtil extends BaseSessionUtil
{
    const COOKIE_NAME = 'ondutymanager-templates';

    /**
     * @param string $token
     * @return void
     * @throws \Exception
     */
    public static function storeTemplates(array $results = []): void
    {
        BaseSessionUtil::storeValue(self::COOKIE_NAME, $results);
    }

    /**
     * @return mixed
     */
    public static function retrieveTemplates()
    {
        return BaseSessionUtil::retrieveValue(self::COOKIE_NAME);
    }

    /**
     * @return mixed
     */
    public static function deleteTemplates()
    {
        BaseSessionUtil::deleteValue(self::COOKIE_NAME);
    }
}
