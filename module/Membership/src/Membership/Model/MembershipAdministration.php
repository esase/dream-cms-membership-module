<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.dream-cms.kg/en/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Dream CMS software.
 * The Initial Developer of the Original Code is Dream CMS (http://www.dream-cms.kg).
 * All portions of the code written by Dream CMS are Copyright (c) 2014. All Rights Reserved.
 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2014 Dream CMS. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Dream CMS software
 * Attribution URL: http://www.dream-cms.kg/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
namespace Membership\Model;

use Application\Service\ApplicationSetting as ApplicationSettingService;
use Application\Utility\ApplicationImage as ImageUtility;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Membership\Exception\MembershipException;
use Membership\Event\MembershipEvent;
use Application\Utility\ApplicationErrorLogger;
use Exception;

class MembershipAdministration extends MembershipBase
{
    /**
     * Upload an image
     *
     * @param integer $membershipId
     * @param array $image
     *      string name
     *      string type
     *      string tmp_name
     *      integer error
     *      integer size
     * @param string $oldImage
     * @param boolean $deleteImage
     * @throws \Membership\Exception\MembershipException
     * @return void
     */
    protected function uploadImage($membershipId, array $image, $oldImage = null, $deleteImage = false)
    {
        // upload the membership's image
        if (!empty($image['name'])) {
            // delete old image
            if ($oldImage) {
                if (true !== ($result = $this->deleteImage($oldImage))) {
                    throw new MembershipException('Image deleting failed');
                }
            }

            // upload a new one
            if (false === ($imageName =
                    FileSystemUtility::uploadResourceFile($membershipId, $image, self::$imagesDir))) {

                throw new MembershipException('Avatar uploading failed');
            }

            // resize the image
            ImageUtility::resizeResourceImage($imageName, self::$imagesDir,
                (int) ApplicationSettingService::getSetting('membership_image_width'),
                (int) ApplicationSettingService::getSetting('membership_image_height'));

            $update = $this->update()
                ->table('membership_level')
                ->set([
                    'image' => $imageName
                ])
                ->where([
                    'id' => $membershipId
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();
        }
        elseif ($deleteImage && $oldImage) {
            // just delete the membership's image
            if (true !== ($result = $this->deleteImage($oldImage))) {
                throw new MembershipException('Image deleting failed');
            }

            $update = $this->update()
                ->table('membership_level')
                ->set([
                    'image' => null
                ])
                ->where([
                    'id' => $membershipId
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();
        }
    }

    /**
     * Add a new role
     *
     * @param array $formData
     *      integer role_id - required
     *      integer cost - required
     *      integer lifetime - required
     *      string description - required
     * @param array $image
     * @return integer|string
     */
    public function addRole(array $formData, array $image)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            if (empty($formData['active'])) {
                $formData['active'] = self::MEMBERSHIP_LEVEL_STATUS_NOT_ACTIVE;
            }

            $insert = $this->insert()
                ->into('membership_level')
                ->values(array_merge($formData, [
                    'language' => $this->getCurrentLanguage()
                ]));

            $statement = $this->prepareStatementForSqlObject($insert);
            $statement->execute();
            $insertId = $this->adapter->getDriver()->getLastGeneratedValue();

            $this->uploadImage($insertId, $image);
            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the add membership role event
        MembershipEvent::fireAddMembershipRoleEvent($insertId);

        return $insertId;
    }

    /**
     * Edit role
     *
     * @param array $roleInfo
     * @param array $formData
     *      integer role_id - required
     *      integer cost - required
     *      integer lifetime - required
     *      string description - required
     *      string image - required
     * @param array $image
     * @return boolean|string
     */
    public function editRole($roleInfo, array $formData, array $image)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            if (empty($formData['active'])) {
                $formData['active'] = self::MEMBERSHIP_LEVEL_STATUS_NOT_ACTIVE;
            }

            $update = $this->update()
                ->table('membership_level')
                ->set($formData)
                ->where([
                    'id' => $roleInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();

            $this->uploadImage($roleInfo['id'], $image, $roleInfo['image']);
            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the edit membership role event
        MembershipEvent::fireEditMembershipRoleEvent($roleInfo['id']);

        return true;
    }
}