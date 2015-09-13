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
return [
    'Membership\Test\MembershipBootstrap'                      => __DIR__ . '/test/Bootstrap.php',
    'Membership\Module'                                        => __DIR__ . '/Module.php',
    'Membership\Model\MembershipBase'                          => __DIR__ . '/src/Membership/Model/MembershipBase.php',
    'Membership\Model\MembershipWidget'                        => __DIR__ . '/src/Membership/Model/MembershipWidget.php',
    'Membership\Model\MembershipAdministration'                => __DIR__ . '/src/Membership/Model/MembershipAdministration.php',
    'Membership\Model\MembershipConsole'                       => __DIR__ . '/src/Membership/Model/MembershipConsole.php',
    'Membership\View\Widget\MembershipUserLevelsWidget'        => __DIR__ . '/src/Membership/View/Widget/MembershipUserLevelsWidget.php',
    'Membership\View\Widget\MembershipAbstractWidget'          => __DIR__ . '/src/Membership/View/Widget/MembershipAbstractWidget.php',
    'Membership\View\Widget\MembershipLevelWidget'             => __DIR__ . '/src/Membership/View/Widget/MembershipLevelWidget.php',
    'Membership\View\Helper\MembershipImageUrl'                => __DIR__ . '/src/Membership/View/Helper/MembershipImageUrl.php',
    'Membership\Event\MembershipEvent'                         => __DIR__ . '/src/Membership/Event/MembershipEvent.php',
    'Membership\Controller\MembershipConsoleController'        => __DIR__ . '/src/Membership/Controller/MembershipConsoleController.php',
    'Membership\Controller\MembershipAjaxController'           => __DIR__ . '/src/Membership/Controller/MembershipAjaxController.php',
    'Membership\Controller\MembershipAdministrationController' => __DIR__ . '/src/Membership/Controller/MembershipAdministrationController.php',
    'Membership\PaymentHandler\MembershipHandler'              => __DIR__ . '/src/Membership/PaymentHandler/MembershipHandler.php',
    'Membership\PagePrivacy\MembershipBuyPrivacy'              => __DIR__ . '/src/Membership/PagePrivacy/MembershipBuyPrivacy.php',
    'Membership\Exception\MembershipException'                 => __DIR__ . '/src/Membership/Exception/MembershipException.php',
    'Membership\Form\MembershipAclRole'                        => __DIR__ . '/src/Membership/Form/MembershipAclRole.php',
    'Membership\Form\MembershipFilter'                         => __DIR__ . '/src/Membership/Form/MembershipFilter.php',
];
