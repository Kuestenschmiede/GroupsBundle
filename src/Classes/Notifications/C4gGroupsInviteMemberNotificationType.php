<?php
    namespace con4gis\GroupsBundle\Classes\Notifications;
    
    use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
    use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
    use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
    // use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

    class C4gGroupsInviteMemberNotificationType implements NotificationTypeInterface
    {
        public const NAME = 'invite_member';

        public function __construct(private TokenDefinitionFactoryInterface $factory)
        {
            $this->factory = $factory;
        }

        public function getName(): string
        {
            return self::NAME;
        }

        public function getTokenDefinitions(): array
        {
    //        $return = array
    // (
    //     'recipients'           => array('member_email','new_member_email'),
    //     'email_subject'        => array('subject'),
    //     'email_text'           => array('member_name','text_content'),
    //     'email_html'           => array('member_name','text_content'),
    //     'email_sender_name'    => array('member_email', 'member_name'),
    //     'email_sender_address' => array('member_email'),
    //     'email_recipient_cc'   => array('member_email'),
    //     'email_recipient_bcc'  => array('member_email'),
    //     'email_replyTo'        => array('member_email'),
    //     'file_content'           => array('member_name','text_content', 'subject'),
    // );
            return [
                $this->factory->create(AnythingTokenDefinition::class, 'member_email', 'admin_email'),
               
            ];
        }
    }
?>