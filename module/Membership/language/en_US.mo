��    =        S   �      8  +   9  -   e  ,   �  /   �  6   �     '     6  )   F     p  '   �  !   �      �  5   �  3   1  -   e  &   �  %   �  (   �  -   	  '   7  '   _  &   �  (   �     �     �  "   �     	  
   *	     5	     G	     ^	  >   n	  <   �	  :   �	     %
     7
     M
     d
     s
  ,   �
     �
     �
  D   �
  A   .  .   p  K   �  	   �     �     �       	             #     ,  	   5     ?     H     Q     Z     s  �  �  %   P  '   v  &   �  )   �  0   �           /  !   ?     a     y     �     �  G   �  E     7   Z  .   �  -   �  0   �  ?      /   `  /   �  .   �  (   �            "   .     Q  
   k     v     �     �  3   �  1   �  /        E     W     m     �     �  ,   �     �     �  D   	  A   N  .   �  K   �                         !     &     *     3     7     <     @     I  q  M  �   �               '            %   $                               "   -      9   :   	   ;   4               <      .      
              *   3   #                      &             /          8         5   6       7                    2      =   1          (   +          ,             !   0       )       ACL - Adding membership roles in admin area ACL - Deleting membership roles in admin area ACL - Editing membership roles in admin area ACL - Editing membership settings in admin area ACL - Viewing list of membership levels  in admin area Buy membership Display options Event - Activating membership connections Event - Adding membership roles Event - Deleting membership connections Event - Deleting membership roles Event - Editing membership roles Event - Membership connection activated by the system Event - Membership connection deleted by the system Event - Membership connection deleted by user Event - Membership role added by guest Event - Membership role added by user Event - Membership role deleted by guest Event - Membership role deleted by the system Event - Membership role deleted by user Event - Membership role edited by guest Event - Membership role edited by user Expiration notification reminder in days Info Lifetime in days List of avaiable membership levels List of membership levels Membership Membership Levels Membership expire date Membership info Membership items width for extra small devices phones (<768px) Membership items width for medium devices desktops (<=992px) Membership items width for small devices tablets (<=768px) Membership levels Membership start date Membership subscribers New membership Purchased membership levels Selected membership levels have been deleted Show the per page menu Show the sorting menu The expiration notification value  must be less than role's lifetime The module allows you buy different membership levels on the site The selected membership level has been deleted You can remind  users about the expiration after N days after the beginning col-md-12 col-md-3 col-md-4 col-md-6 col-sm-12 col-sm-3 col-sm-4 col-sm-6 col-xs-12 col-xs-3 col-xs-4 col-xs-6 membership_install_intro membership_uninstall_intro Project-Id-Version: Dream CMS
POT-Creation-Date: 2013-07-19 11:59+0600
PO-Revision-Date: 2015-08-23 12:00+0600
Last-Translator: AlexE <alexermashev@gmail.com>
Language-Team: 
Language: English
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Generator: Poedit 1.5.4
X-Poedit-KeywordsList: _;gettext;gettext_noop
X-Poedit-Basepath: .
X-Poedit-SourceCharset: UTF-8
Plural-Forms: nplurals=2; plural=(n != 1);
 Adding membership roles in admin area Deleting membership roles in admin area Editing membership roles in admin area Editing membership settings in admin area Viewing list of membership levels  in admin area Buy membership Display options Activating membership connections Adding membership roles Deleting membership connections Deleting membership roles Editing membership roles The membership's connection with id - "%d" was activated by the system. The membership's connection with id - "%d" was deleted by the system. "%s" deleted the membership's connection with id - "%d" Guest added the membership role with id - "%d" "%s" added the membership role with id - "%d" Guest deleted the membership role with id - "%d" The membership's role with id - "%d" was deleted by the system. "%s" deleted the membership role with id - "%d" Guest edited the membership role with id - "%d" "%s" edited the membership role with id - "%d" Expiration notification reminder in days Info Lifetime in days List of avaiable membership levels List of membership levels Membership Membership Levels Membership expire date Membership info Items width for extra small devices phones (<768px) Items width for medium devices desktops (<=992px) Items width for small devices tablets (<=768px) Membership levels Membership start date Membership subscribers New membership Purchased membership levels Selected membership levels have been deleted Show the per page menu Show the sorting menu The expiration notification value  must be less than role's lifetime The module allows you buy different membership levels on the site The selected membership level has been deleted You can remind  users about the expiration after N days after the beginning 100% 25% 33.3333% 50% 100% 25% 33.3333% 50% 100% 25% 33.3333% 50% For correct module's work you  need:<br />1. Activate the module's page ("Pages management" - "Add system pages" - "Choose the buy membership page")<br />2. Add this command into your cron jobs (it helps you remove all expired membership levels): <br/><b>*/1 * * * * /usr/bin/php /your_project_root/public/index.php membership clean expired connections &> /dev/null</b> Delete this cron job command: <br/><b>*/1 * * * * /usr/bin/php /your_project_root/public/index.php membership clean expired connections &> /dev/null</b> 