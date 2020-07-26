<?php
    session_start();
?>
<!doctype html>
<head>
    <meta charset="utf-8">
    <meta name="viewsport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>The Daily Paper</title>
    <!-- Bootstrap CSS -->
    <link rel ="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
</head>
<body>
    <?php
        // define global variables. 
        // They could be 'Afghanistan', 'AF' repectively.
        $country_name = $country_code = "";
        
        $_SESSION["country_name"] = "";
        $_SESSION["keyword"] = "";
        
        // connect to database
        $servername = "localhost";
        $username = "rxzpnkmyzd";
        $password = "Aa123456789";

        try {
            $conn = new PDO("mysql:host=$servername; dbname=rxzpnkmyzd", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "Connected successfully";
            
            // create table country
            /* $tb = "CREATE TABLE country (Code VARCHAR(5) UNIQUE, Name VARCHAR(50) UNIQUE);";
               $conn->exec($tb); */
            
            // retrieve country list
            $country_response = file_get_contents('https://pkgstore.datahub.io/core/country-list/data_json/data/8c458f2d15d9f2119654b29ede6e45b8/data_json.json');
            $country_array = json_decode($country_response, true);

            // insert country list to the table 'country'
            /*foreach($country_array as $country){
                //$sql = "INSERT INTO country (Code, Name) VALUES (".$country['Code'].",".$country['Name'].")";
                $sql = 'INSERT INTO country (Code, Name) VALUES ('.'"'.$country["Code"].'"'.','.'"'.$country["Name"].'"'.')';
                $conn->exec($sql);
            }*/

    ?>
    <div class="jumbotron">
        <h1 class="display-4 text-center">The Daily Paper</h1>
        <hr class="my-4">
        <form class="text-center" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
            <select class="width-30p height-40px margin-5px" name = 'country_name'>
            <?php
                // insert names for 248 countries
                foreach($country_array as $country){
                    ?><option id = <?php echo '"'.$country['Name'].'"' ?>><?php 
                    echo $country['Name'];
                    ?></option><?php
                }
            ?>
            </select>
            <br>
            <input type="text" class="width-30p height-40px margin-5px" name="key" id="keyword"><br>
            <input class="btn btn-primary width-30p height-40px margin-5px" type="submit" value="Submit">
        </form>
        <div class="text-center">
            <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // import teh global variables
                    global $country_name, $country_code, $key;

                    // get the value of the select tag, eg, the country name.
                    $country_name = $_POST["country_name"];

                    // update session, which is the country name in the previous select tag
                    $_SESSION["country_name"] = $country_name;

                    // keep record of the keyword before page refresh
                    $_SESSION["keyword"] = $_POST["key"];

                    // get the 2-letter country code according to the country name.
                    $stmt = $conn->query('select Code from country where Name = '.'"'.$country_name.'"');
                    $country_code = $stmt->FETCH(PDO::FETCH_ASSOC);

                    // $stmt->FETCH(PDO::FETCH_ASSOC) returns an array so need to be more specific here
                    $country_code = $country_code['Code'];
                }
                // convert country code to lower case. For example, 'AF' to 'af'
                $country_code = strtolower($country_code);

                // retrieve top 10 news from News API
                $response = file_get_contents('https://newsapi.org/v2/top-headlines?pageSize=10country='.$country_code.'&q='.$_SESSION["keyword"].'&apiKey=db99c3dc50e84ac280144f02b64119d1');
                $response_JSON_array = json_decode($response, true);

                // display top 10 news 
                foreach($response_JSON_array['articles'] as $element){
                    // $title is the headline
                    $title = $element['title'];
                    // make sure that the headline contains the keyword (regardless of uppercase or lowercase)
                    if((strpos(strtolower($title), strtolower($_SESSION["keyword"])) !== false && $_SESSION["keyword"] != "") || $_SESSION["keyword"] == "" ){
            ?><h5 class="text-center"><?php print_r($title);?></h5>
            <p>
                <?php
                        echo "<br>";
                        print_r($element['description']);
                        echo "<br>";
                    } // if statement ends 
                } // foreach ends

                } catch(PDOException $e) {
                    echo "Connection failed: " . $e->getMessage();
                  }

                // connection ends
                $conn = null;
                ?>
            </p>
        </div>
    <script>
        // having the option tag which has the country name you searched selected.
        document.getElementById(<?php echo '"'.$_SESSION["country_name"].'"' ?>).selected = "selected";
        // the input box keeps the value before page refresh occurs
        document.getElementById("keyword").value = <?php echo '"'.$_SESSION["keyword"].'"' ?>;
    </script>
    </div>
</body>
</html>
