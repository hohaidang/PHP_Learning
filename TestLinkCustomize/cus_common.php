<?php
class cust_TestCase {
    public $name = null;
    public $exe_time = null;
    function __construct($case_name, $exe_time)
    {
        $this->name = $case_name;
        $this->exe_time = $exe_time;
    }
}

class cust_TestType {
    public $name = null;
    public $tbl_cases = array();
    function __construct($testType_name) {
        $this->name = $testType_name;
    }
    public function addTestCase($testcase) {
        array_push($this->tbl_cases, $testcase);
    }

    public function numOfTestCase() {
        return count($this->tbl_cases);
    }

    public function getArverageExecuteTime() {
        $total_time = 0;
        $total_TCs = count($this->tbl_cases);
        foreach ($this->tbl_cases as $testcase) {
            $total_time += $testcase->exe_time;
        }
        if($total_TCs == 0)
            return 0;
        else
            return ($total_time / $total_TCs);
    }

    public function getTotalExePerType() {
        // Get Total execute time in hours
        $total_time = 0;
        foreach ($this->tbl_cases as $testcase) {
            if(($testcase->exe_time != null) and ($testcase->exe_time != 0))
            {
                $total_time += $testcase->exe_time;
            }
        }
        return $total_time / 60;
    }
}

class cust_TestSuite {
    public $name = null;
    public $tbl_cases = array();
    function __construct($suite_name)
    {
        $this->name = $suite_name;
    }
    public function addTestCase($testcase)
    {
        array_push($this->tbl_cases, $testcase);
    }
    public function getTotalExePerSuite()
    {
        // Get Total execute time in hours
        $total_time = 0;
        foreach ($this->tbl_cases as $testcase) {
            if(($testcase->exe_time != null) and ($testcase->exe_time != 0))
            {
                $total_time += $testcase->exe_time;
            }
        }
        return $total_time / 60;
    }

    public function getArverageExecuteTime() {
        $total_time = 0;
        $total_TCs = count($this->tbl_cases);
        foreach ($this->tbl_cases as $testcase) {
            $total_time += $testcase->exe_time;
        }
        if($total_TCs == 0)
            return 0;
        else
            return ($total_time / $total_TCs);
    }
}

class cust_Tester {
    public $name = null;
    public $tbl_suite = array();
    public $tbl_testType = array();

    public function addTestSuite($test_suite)
    {
        array_push($this->tbl_suite, $test_suite);
    }

    public function addTestType($test_type) {

    }
    public function getTotalExePerTester()
    {
        // Get total of execution time in hour per tester
        $total = 0;
        foreach ($this->tbl_suite as $suite) {
            $total += $suite -> getTotalExePerSuite();
        }
        return $total;
    }

    public function getTotalExePerTesterType()
    {
        // Get total of execution time in hour per tester
        $total = 0;
        foreach ($this->tbl_testType as $type) {
            $total += $type->getTotalExePerType();
        }
        return $total;
    }

    public function getTotalAvgExeTimePerMainType() {
        $total = 0;
        if (empty($tbl_testType)) return 0;
        foreach ($this->tbl_testType as $mainType) {
            foreach ($mainType as $subType) {
                $total += $subType->getArverageExecuteTimePerType();
            }
        }
        return $total;
    }

    public function getTotalAvgExeTime()
    {
        $total = 0;
        foreach ($this->tbl_suite as $suite) {
            $total += $suite->getArverageExecuteTime();
        }
        return $total;
    }

    public function getTotalAvgExeTimeForType()
    {
        $total = 0;
        foreach ($this->tbl_testType as $type) {
            $total += $type->getArverageExecuteTime();
        }
        return $total;
    }
    public function getTotalTestCase()
    {
        $sum = 0;
        foreach ($this->tbl_suite as $suites)
        {
            $sum += count($suites->tbl_cases);
        }
        return $sum;
    }
    public function getTotalTestCaseForType() {
        $sum = 0;
        foreach ($this->tbl_testType as $type) {
            $sum += count($type->tbl_cases);
        }
        return $sum;
    }

    public function getTotalTestCaseForMainType($mainName) {
        $sum = 0;
        foreach ($this->tbl_testType as $type) {
            $mainType = substr($type->name, 0, strpos($type->name, "_"));
            if (strcmp($mainType, $mainName) == 0) {
                $sum += count($type->tbl_cases);
            }
        }
        return $sum;
    }

    public function getAverageTimeForMainType($mainName) {
        $numOfTCs = 0;
        $sumExeTime = 0;
        foreach ($this->tbl_testType as $type) {
            $mainType = substr($type->name, 0, strpos($type->name, "_"));
            if (strcmp($mainType, $mainName) == 0) {
                $numOfTCs += count($type->tbl_cases);
                foreach ($type->tbl_cases as $testcase) {
                    $sumExeTime += $testcase->exe_time;
                }
            }
        }
        if($numOfTCs == 0) return 0;
        else return $sumExeTime/$numOfTCs;
    }

    public function getTotalHourPerMainType($mainName) {
        $totalHour = 0;
        foreach ($this->tbl_testType as $type) {
            $mainType = substr($type->name, 0, strpos($type->name, "_"));
            if (strcmp($mainType, $mainName) == 0) {
                $totalHour += $type->getTotalExePerType();
            }
        }
        return $totalHour;
    }
}