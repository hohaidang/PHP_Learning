<?php
require __DIR__ . '/functions.php';
var_dump(getArray());
echo hello("Zura");
$name_one = array("Zack"=>"Zara", "Anthony"=>"Any",
    "Ram"=>"Rani", "Salim"=>"Sara",
    "Raghav"=>"Ravina");
$rmqt_table = array(
    array("rqmt" => null, "count" => null)
);
echo "Accessing the elements directly:\n";

echo $name_one["Ram"], "\n";
echo $name_one["Raghav"], "\n";

$favorites = array(
    "UI" =>
        array(
            "ScreenTransition",
            "AppearanceBehavior",
            "Wording",
            "InputValidation",
        ),
    "Func" =>
        array(
            "SingleFunction(SingleParam)",
            "SingleFunction(MultiParam)",
            "MultiFunction",
            "FunctionalTest",
            "OutputVerification",
        ),
    "Scenario" =>
        array(
            "NormalSystem",
            "SemiNormalSystem",
            "AbnormalSystem",
            "StressTest",
            "StableOperation",
        ),
    "GenScenario" =>
        array(
            "NormalSystem",
            "SemiNormalSystem",
            "StressTest",
            "StableOperation",
        ),
);
$data = [];
foreach($favorites as $data)
{
    echo $data;
}
$arr = array ('first' => 'a', 'second' => 'b', );
$key = array_search ('a', $arr); // find key in array
print_r ($favorites);