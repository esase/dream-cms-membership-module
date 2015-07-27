<?php

namespace Membership\Model;

use Application\Service\ApplicationSetting as ApplicationSettingService;
use Application\Utility\ApplicationImage as ImageUtility;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Membership\Exception\MembershipException;
use Membership\Event\MembershipEvent;
use Application\Utility\ApplicationErrorLogger;
use Exception;

/*
use Exception;
use Membership\Exception\MembershipException;
use Application\Service\Service as ApplicationService;
use Application\Utility\ErrorLogger;
use Application\Utility\FileSystem as FileSystemUtility;
use Application\Utility\Image as ImageUtility;
use Membership\Event\Event as MembershipEvent;
*/

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
     * @throws MembershipException
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
                    'image' => ''
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
        $insertId = 0;

        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

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
}