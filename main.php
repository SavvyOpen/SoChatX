<html>

<!--
	Copyright (c) 2025 Chung Lim Lee and Savvy Open
	All rights reserved.
-->





<head>
    <title>SO ChatX</title>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
</head>


<body tabindex="-1">

<?php include $_SERVER['DOCUMENT_ROOT']. "/script/lib/php/modal_window.php" ?>
<script src="/script/lib/js/server_request.js"></script>





<!-- CSS LAYOUTS -->

<style>

body {font-family: Arial,sans-serif; font-weight: 500; background-color: #f0f0f8; overflow: hidden; margin: 0px} /* *** margin here must set to zero, default is 8px */

input {font-size: 15px; height: 24px;}

button {box-shadow: 0px 0px 3px #303030; border: none; border-radius: 10px; padding: 5px 10px 5px 10px;}
button:hover {background-color: white; cursor: pointer;}


.contact {width: 92%; max-width: 700px; padding-left: 4%; padding-top: 1%; padding-bottom: 2%; padding-right: 5%; border: 1px solid #f0f0f0}
.contact:hover {background-color: f0f0f0}
.user {float: left; font-size: 1.2em; color: black}
.date_time {float: right; font-size: 1em; color: grey}
.last_message {font-size: 1em; color: grey}
.contact_view_button {font-size: 1em; width: 25%; padding: 8px 0px 8px 0px; float: left;}


#chat_title {float: left; width: 93%; padding: 10px 0px 3px 0px; text-align: center; height: 30px; font-size: 1.2em; color: white; background-color: black}    
#back_button {float: right; width: 7%; padding: 10px 0px 3px 0px; text-align: center; height: 30px; font-size: 1.2em; color: white; background-color: black}    
#back_button:hover {cursor: pointer}

#contact_tabs {position: absolute; width: 100%; max-width: 700px; color: black; background-color: #e8e8e8; overflow: auto; box-shadow: 0px -2px 5px #c0c0c0;}
#contact_tabs:hover {cursor: pointer;}

#chat_button:hover, #group_button:hover, #add_new_chat:hover, #option_button:hover {background-color: #f2f2f2;}

#history_box {position: absolute; width: 99%; max-width: 700px; height: 500px; margin-left: 1%; top: 44px; left: 0px;}

#message_box {font-size: 25px; position: absolute; bottom: 0px; left: 0px; font-family: Arial,sans-serif; font-size: 1em; border: none; width: 80%; max-width: 560px; height: 100px; padding: 15px; background-color: white; resize: none} 
#message_box:focus {outline: none}

#send_button {position: absolute; bottom: 0px; right: 0px; text-align: center; width: 20%; max-width: 140px; height: 100px; font-size: 1.2em; color: black; background-color: #f8f8f8}
#send_button:hover {cursor: pointer; background-color: white;}


.no_text_select {
    
    -ms-user-select: none;
    -moz-user-select: none;
    -webkit-user-select: none;
    user-select: none;
}

</style>    
    




<!-- HTML LAYOUTS -->

<div id="master_view" style='box-shadow: 0px 0px 12px grey; background-color: white; width: 100%; height: 100%; max-width: 700px; margin-left: auto; margin-right: auto;'>

    <div id="contact_view" style="position: absolute; width: 100%; max-width: 700px; height: 100%; top: 0px;" class="no_text_select">
    
        <div id="logo" style="text-align: center; padding: 10px 0px 3px 0px; width: 100%; height: 30px; background-color: black; color: white; font-size: 1.2em;">SO ChatX
        </div>
        
        <div id="contact_list" style="overflow-y: auto; overflow-x: hidden; height: 80%;"></div>
        
        <div id="contact_tabs">
            <center>
                <span id="chat_button" style="height: 100%; background-color: white" onclick="use_contact_type = 'chat'; get_contact_data(); this.style.backgroundColor = 'white'; group_button.style.backgroundColor = '';" class="contact_view_button">"!"<br>Chats</span>
                <span id="group_button" onclick="use_contact_type = 'group'; get_contact_data(); this.style.backgroundColor = 'white'; chat_button.style.backgroundColor = '';" class="contact_view_button">@<br>Groups</span>
                <span id="add_new_chat" class="contact_view_button">+<br>Add</span>
                <span id="option_button" onclick="modal_window('Options', `<center><br><br>TOTP Authenticator<br><button style='margin-top: 8px;' onclick='totp_setup();'>Setup</button><br><br><br><br>Active Session<br><button style='margin-top: 8px;' onclick='logout();'>Logout</button><br><br></center>`)" class="contact_view_button"><b>···</b><br>Options</span>
            </center>        
        </div>
    
    </div>
    
    
    
    <div id="chat_view" style="position: absolute; display: none; width: 100%; height: 100%; max-width: 700px; top: 0px;">
    
        <div id="chat_title" class="no_text_select"></div><div id="back_button"  class="no_text_select" onclick="contact_view.style.display = 'block'; chat_view.style.display = 'none'; message_box.value = ''">x</div>
        
        <div id="history_box" style="overflow-y: scroll;"></div>
    
        <div id="message_container" style="position: absolute; bottom: 0px; left: 0px; box-shadow: 0px 0px 7px #d0d0d0; width: 100%; max-width: 700px; height: 100px;">
            <textarea id="message_box"></textarea>
            <div id="send_button" class="no_text_select"><br><br>Send</div>
        </div>

    </div>

</div>





<!-- JS -->

<script>
    
var contact_view = document.getElementById('contact_view');
var contact_list = document.getElementById('contact_list');

var chat_view = document.getElementById('chat_view');
var chat_title = document.getElementById('chat_title');
var history_box = document.getElementById('history_box');
var message_box = document.getElementById('message_box');
var send_button = document.getElementById('send_button');

var all_chat_data = []; // all contacts with their respective messages
var all_chat_data_new;  // all new chat data coming from server
var all_chat_data_version = {};

var use_contact_type = 'chat';  // set the corresponding view for chat or group
var currently_interacting_with_user = '';
var _derived_password = '';



document.getElementById('contact_tabs').style.bottom = '0px';   // set contact tabs always at the bottom of the relative screen size



document.getElementById('add_new_chat').onclick = function() {
    
    modal_window('Add Menu', `
    
        <div style='overflow-y: hidden;'>
            <center>
                <b>User Chat Request</b><br>
                <input type="text" id="request_chat_with" size=18 style='border: 1px solid grey; margin-top: 8px;' placeholder='case-sensitive'><br><br>
                <button id="request_chat_with_button">Request</button>
                <br><br><br>
                
                
                <b>Group</b><br>
                <input type="text" id="group_name_input" size=18 style='border: 1px solid grey; margin-top: 8px;' placeholder='case-sensitive'><br><br>
                <button onclick='if (group_name_input.value !== "") create_group(group_name_input.value);'>Create</button>
                <button onclick='if (group_name_input.value !== "") join_group(group_name_input.value);' style='margin-left: 30px;'>Join</button>
                <br><br>
            </center>
        </div>
    `);
    
    request_chat_with_button.onclick = function() { 
        
        if (request_chat_with.value !== '')
            request_chat(request_chat_with.value);

    };
}





// RESIZE HISTORY BOX WHEN VIEW PORT SIZE CHANGES

window.addEventListener('resize', () => {

    history_box.style.height = window.innerHeight - 30 - 100 - 28;  // minus heading, contact_tabs, adjustment
});





// LOGIN HANDLINGS

function login_modal(force_show) {
    
    if (modal_window_box.style.display === '' || modal_window_box.style.display === 'none' || force_show === true) {

        modal_window('Sign In', `
        
            <center>
            <div style='overflow: hidden'>
            User Name<br>
            <input style='border: 1px solid grey; margin-top: 8px;' type="text" id="_user_name" placeholder="case-sensitive">
            <br><br>
            Password<br>
            <input style='border: 1px solid grey; margin-top: 8px;' type="password" id="_user_password" placeholder="">
            <br><br>
            
            <input id="_totp_code" style='border: 1px solid grey; margin-top: 8px;' type="text" placeholder="TOTP code" size=8>
            <br><br><br>
            <button style='margin-left: 1px;' onclick="pre_login();">Login</button>
            <br><br>
            </div>
            </center>
        `);
    }
}

async function get_key_by_pbkdf2(password, salt) {

    var key = await window.crypto.subtle.importKey("raw", new TextEncoder().encode(password), "PBKDF2", false, ["deriveBits", "deriveKey"]);
    
    var derived_key_bits = await crypto.subtle.deriveBits(
        
        {
            name: 'PBKDF2',
            salt: new TextEncoder().encode(salt),
            iterations: 1_000_000,
            hash: 'SHA-512'
        },
        
        key,
        256
    );

	return new Uint8Array(derived_key_bits).toString();
}

async function pre_login() { // get the password salt for the given user (*** this is for client-side hashing for added security on top of the server side hashing))

    var http_post = new XMLHttpRequest();

    http_post.onreadystatechange = async function() {
      
    	if (this.readyState === this.HEADERS_RECEIVED)
    		res = http_post.getAllResponseHeaders();
    		
    
    	if (this.readyState==4 && this.status==200) {
 
    		var response = JSON.parse(this.responseText);

            if (response.timeout) {
            
                modal_window('Login Timeout', 'Please wait for 5 seconds before retry.');
                return;
            }

            var user_password_salt = response.password_salt;
            var derived_password = await get_key_by_pbkdf2(_user_password.value, user_password_salt);
                
            login(derived_password);
    	}
    }
    
    http_post.open("POST", '/app/so_login/so_login_salt.php');
    http_post.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    http_post.send("user=" + encodeURIComponent(_user_name.value));
}

function login(derived_password) {  // log in when no existing user already logged in with the current session id in the client's browser

    var http_post = new XMLHttpRequest();

    http_post.onreadystatechange=function() {
      
    	if (this.readyState === this.HEADERS_RECEIVED)
    		res = http_post.getAllResponseHeaders();
    		
    
    	if (this.readyState==4 && this.status==200) {
 
    		var response = JSON.parse(this.responseText);

    		if (response.success) {

                // close modal box and remove credential from temporary elements
                
                _user_name.value = '';
                _user_password.value = '';
                _totp_code.vlaue = '';
                
                modal_window_close();
                location.reload();  // *** force page reload
    		}

            else {
                
                modal_window('Login Failed', 'Credentials are incorrect.<br><br><button style="margin-left: 1px;" onclick="login_modal(true);">Retry</button>');
            }
    	}
    }
    
    http_post.open("POST", '/app/so_login/so_login.php');
    http_post.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    http_post.send("user=" + encodeURIComponent(_user_name.value) + "&password=" + encodeURIComponent(derived_password) + "&totp_code=" + encodeURIComponent(_totp_code.value));
}





// ********** THIS IS A DEMO CLIENT ONLY **********

// The remaining script is intentionally omitted for security reasons.
// Full client + server implementation is available under commercial license.
  
</script>


</body>    
</html>
