<?php
namespace Swipp\api;
/**
 * notificationResource.php
 * michaelmeyers
 * 8/16/12 2:22 PM
 */
class notificationResource extends baseResource
{
    public static $NOTIFICATION_BIRTHDAY = 1;
    public static $NOTIFICATION_FOLLOWERS = 2;
    public static $NOTIFICATION_NEW_FRIENDS = 3;
    public static $NOTIFICATION_TRACKING_TERMS = 4;
    public static $NOTIFICATION_YOU_GOT_SWIPPED = 5;
    public static $NOTIFICATION_NEW_MESSAGES = 6;
    public static $NOTIFICATION_COMMENT_ON_COMMENT = 7;

   public function getSettings()
   {
       return $this->send("swipp/notifications/settings");
   }

   public function setSettings(array $type,array $app_flag,array $email_flag)
   {

       $length = count($type);
       if ($length == 0)
           return false;
       $body = array();
       for ($i = 0;$i < $length;$i++)
            $body['values']['list'][] = array("type"=>$type[$i],"app"=>$app_flag[$i],"email"=>$email_flag[$i]);
       $body = json_encode($body);
       return $this->send("swipp/notifications/settings",null,uriRequestCore::PUT,$body);
   }

    public function getNotifications($notificationType = null,
                                     $status = null,
                                     $pageSize=50,
                                     $pageNumber=1,
                                     $sortColumn = 'createtime',
                                     $sortOrder='desc')
    {
        $params = array( 'notificationType' => $notificationType,
            'status'=>$status,'pageSize' => $pageSize, 'pageNumber' => $pageNumber,
            'sortColumn' => $sortColumn, 'sortOrder' => $sortOrder
        );

        $response = $this->send("swipp/notifications", $params);
        return $response;
    }

    public function putNotifications($notificationIds)
    {
        $params = array(
            'notificationIds' => $notificationIds
        );
        $body = " ";
        $response = $this->send("swipp/notifications", $params, uriRequestCore::PUT, $body);
        return $response;
    }

}
