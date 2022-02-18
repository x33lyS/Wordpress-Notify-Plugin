<?php
/**
 * @package SendDiscordNotify
 * @version 1.0.0
 */
/*
Plugin Name: SendNotify
Plugin URI : https://swebystudio.com
Description: A notify plugin for Discord, Telegram and Mail
Author: Florian et Adam
Version: 1.0.0
Author URI: https://swebystudio.com
*/


// Discord Settings Page
function add_settings_page() {
    add_options_page( 'Discord key Notify Plugin', 'Discord Key', 'manage_options', 'notify-plugin', 'print_menu' );
}
add_action( 'admin_menu', 'add_settings_page' );

function print_menu() {
    ?>
    <h2>Set yout Discord key</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'fields_option' );
        do_settings_sections( 'print_form' ); 
        ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function dbi_register_settings() {
    register_setting( 'fields_option', 'fields_option', 'dbi_example_plugin_options_validate' );
    add_settings_section( 'api_settings', 'API Settings', 'dbi_plugin_section_text', 'print_form' );
    add_settings_field( 'dbi_plugin_setting_api_key', 'Discord Key', 'dbi_plugin_setting_api_key', 'print_form', 'api_settings' );
}
add_action( 'admin_init', 'dbi_register_settings' );


// function dbi_example_plugin_options_validate( $input ) {
//     echo $input;
//     $newinput['api_key'] = trim( $input['api_key'] );
//     if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {
//         $newinput['api_key'] = '';
//     }
//     return $newinput;
// }


function dbi_plugin_section_text() {
    echo '<p>Here you can set your weebhook key</p>';
}


function dbi_plugin_setting_api_key() {
    $options = get_option( 'fields_option' );
    echo "<input id='dbi_plugin_setting_api_key' name='fields_option[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' />";
    $webhooks = $options['api_key'];
    echo "\nYour key is : " . $webhooks;
    return $webhooks;
}


// Discord Notify
function discordNotif($comment_id, $comment_approved, $comment_data){
    $url = dbi_plugin_setting_api_key();
    $name = get_bloginfo('name');
    $blogDescription = get_bloginfo('description');
    $currentUrl = get_bloginfo('url');
    $title = "Comment from : " . $currentUrl;
    $description = json_encode([
        "username" => "Antoine Roques",
        'avatar_url' => 'https://www.cathobel.be/wp-content/uploads/2020/10/Dieu.jpg',
        'content' => "You recceive a new comment from your website : $name",
        'timestamp' => date('c',strtotime('now')),
        'tts' => false,
        'embeds' => [
            [
                'title' => $title,
                'type' => "rich",
                'description' => $comment_data['comment_content'],
                //'url' => $urlWebsite,
                "color" => hexdec( "FF0000" ),
                "author" => [
                    'name' => $comment_data['comment_author'],
                    'url' => $comment_data['comment_author_url'],
                    ],
                "fields" => [
                      [
                    "name" => "Author Email :",
                    "value" => $comment_data['comment_author_email'],
                    "inline" => false
                ],
                   
                // Field 1
                [
                    "name" => 'Blog Description : ',
                    "value" => $blogDescription,
                    "inline" => true
                ],
                // Field 2
                [
                    "name" => 'Post Number : ',
                    "value" => $comment_data['comment_post_ID'],
                    "inline" => true
                ],
            ],
                
                // Footer object
                "footer" => [
                    "text" => "Sweby Studio",
                    "icon_url" => "https://www.swebystudio.com/assets/images/favicon.ico"
                ],
            ]
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
$curl = curl_init();
curl_setopt($curl,CURLOPT_URL, $url);
curl_setopt($curl,CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($curl,CURLOPT_POSTFIELDS, $description);
curl_exec($curl);
curl_close($curl);
}


add_action('comment_post', 'discordNotif',10, 3);


//Mail Notify
function mailNotif( $comment_id, $comment_approved ) {
    
        $comment = get_comment( $comment_id );
        $mail = 'swebystudio@gmail.com';
        $subject = sprintf( 'New Comment by : %s', $comment->comment_author );
        $message = sprintf( 'Description : %s', $comment->comment_content);
        wp_mail( $mail, $subject, $message);
}


add_action( 'comment_post', 'mailNotif', 10, 2 );


//Telegram Notify
function telegramNotify( $comment_id, $comment_approved, $comment_data ) {
    $apiToken = "5275941976:AAGS6R28tO--9aedlLB6_VlNDyXPt7p0ruU";
    $name = get_bloginfo('name');
    $websiteInfo = get_bloginfo('url');
    $blogDescription = get_bloginfo('description');
    $datas = "||                       || Website : ".  $name . " ||                       ||" ."\n\nComment from : " . $comment_data['comment_author'] .
    "\nMessage is : " . $comment_data['comment_content'].
    "\n\nFrom the Website : " . $websiteInfo ."\nBlog Description : " .$blogDescription.
    "\n\nAuthor Email : " .  $comment_data['comment_author_email'].
    "\nPost Number : " . $comment_data['comment_post_ID'];
    $data = [
        'text' => $datas,
        'chat_id' => '-777072330'
    ];
  $response = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data) );
}


add_action( 'comment_post', 'telegramNotify', 10, 3 );

?>