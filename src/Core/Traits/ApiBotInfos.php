<?php

namespace Mmb\Core\Traits;

use Mmb\Core\Updates\Infos\UserInfo;

trait ApiBotInfos
{

    /**
     * Get robot info
     *
     * @return ?UserInfo
     */
    public function getMe()
    {
        return $this->makeData(
            UserInfo::class,
            $this->request('getMe', [])
        );
    }

}
