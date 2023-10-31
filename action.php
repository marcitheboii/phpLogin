<?php
//global variable, id of the user in sight, calculated by the email(database and password.txt has to be in same order)
$id = 0;

//read in password.txt each line into new index of array 
$passwordArray = file("password.txt", FILE_IGNORE_NEW_LINES);

//key to decode
const key = [-5,14,-31,9,-3];

//helper function not to overindex the key const
function increment_index($index) {
    $index += 1;
    if ($index > count(key)-1) $index = 0;
    return $index;
}

//array of decoded password.txt
$decoded = [];

for($i = 0; $i < count($passwordArray); $i++) {

    //one line at the time
    $singleLineArray = str_split($passwordArray[$i]);

    $index = 0;
    $cracked = [];
    $solved = "";  

    for ($j = 0; $j < count($singleLineArray); $j++) {
        //change each char by the key
        $cracked[$j] =  ord($singleLineArray[$j]) + key[$index];
        $index = increment_index($index);
        //from decimal back to ascii, building up the whole string
        $solved .= chr($cracked[$j]);
    }

    //array of decoded lines
    $decoded[$i] = $solved;
}


//vaild username?
function is_validUsername($decoded){

    $username = $_POST["uname"];

    for( $i = 0; $i < count($decoded); $i++) {
        if($username == substr($decoded[$i],0,strlen($username))) {
            if((substr($decoded[$i],strlen($username),1)== "*")){
                $GLOBALS['id'] = $i;
                return true;
            }
        }
    }
    return false;
}

//valid password? !!use only after is_validUsername!!
function is_validPassword($decoded){
    $username = $_POST["uname"];
    $userpsswd = $_POST["passwd"];
    $loginDetails = $username . "*" . $userpsswd;

    for( $i = 0; $i < count($decoded); $i++) {

        if($loginDetails == $decoded[$i]) return true;
    }

    return false;
}

//mysql kapcsolat a színek kinyeresehez
function get_color($id){

    $conn = new mysqli("localhost", "root", "root1234", "adatok");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //szín lekérése kimentett id alapján
    $result = $conn->query("select titkos from tabla where sor = " . $id+1 . " limit 1;");

    $color = mysqli_fetch_row($result)[0];

    return $color;
}

//magyarul van megadva a szín a db-ben ezért át kell fordítani angolra html/css miatt
function translater($text) {
    $colorMap = [
        "piros" => "red",
        "zold" => "green",
        "sarga" => "yellow",
        "kek" => "blue",
        "fekete" => "black",
        "feher" => "white",
        "narancs" => "orange",
        "barna" => "brown",
        "szurke" => "gray",
        "lila" => "purple",
        "rozsa" => "pink",
        "arany" => "gold",
        "ezust" => "silver",
    ];
    return isset($colorMap[$text]) ? $colorMap[$text] : "";
}

//getting the color from the db and setting it as bg color
function finish($decoded,$id){
    $color = get_color($id);
    $color = translater($color);
    echo   "<html style='background-color: lightgray;''>
            <div style='
                margin: auto;
                margin-top: 5rem;
                border-radius: 40%;
                width: 40em;
                height: 40em;
                background-color:".$color.";
                '></div>
            </html>";
}

//validate
if(is_validUsername($decoded)){
    if(is_validPassword($decoded)){
        finish($decoded,$id);
    }else{
        header("Location: redirect.php");
    }
}else {
    header("Location: usernotfound.php");
}
?>