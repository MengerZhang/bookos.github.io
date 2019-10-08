<?php
    /**
     * 登记接口描述：
     * 作用：用于储存登记时的个人信息
     * 
     * 参数传入方法：使用POST或者GET方法传递参数
     * 参数名：
     * @student_number：学号，字符型，长度13
     * @name：名字，字符型，长度18
     * @sex：性别，字符型，长度3
     * @college：学院，字符型，长度128
     * @major：专业，字符型，长度128
     * @grade：年级，字符型，长度4
     * @phone_number：电话号码，字符型，长度11
     * @other：其它信息，字符型，长度255
     * 
     * 参数返回信息，请查看GiveEcho函数说明
     */

    # 设定字符集
    header('Content-Type:Application/json;charset=utf-8');
    #header('Content-Type:text/html;charset=utf-8');
    
    # 换行标志
    global $changeline;
    $changeline = '<br/>';
    #$changeline = '\n>';
    
    # 程序调试
    global $debug;
    $debug = false;
    
    /**
     * GiveEcho - 给调用者一个结果的响应
     * @code: 状态码
     * @message: 状态信息
     * @data: 返回数据
     * 
     * @return：结束当前程序
     * 
     * 状态码解释：
     * 1xx：表示成功
     * 2xx：表示失败
     * 
     * 100：更新旧数据成功
     * 101：插入新数据成功
     * 
     * 200：连接数据库失败
     * 201：数据库设置失败
     * 202：创建数据表格失败
     * 203：更新表格数据失败
     * 204：插入表格数据失败
     * 
     * 210：传入的参数长度有误
     * 211：传入的参数格式有误
     * 
     * 当执行结束时，会给调用者一个json数据
     */
    function GiveEcho($echoinfo, $connect) {
        global $changeline;
        global $debug;

        $json = json_encode($echoinfo);
        
        mysqli_close($connect);
        
        if ($debug) {
            echo ">>>显示ECHO信息：".$changeline;
            echo $echoinfo['code'].$changeline;
            echo $echoinfo['message'].$changeline;
            echo $echoinfo['data'].$changeline; 
        }
        
        echo ($json);
        die;
    }

    /**
     * SafeHandle - 对数据进行安全处理
     * @data: 要处理的数据
     * 
     * @return：返回处理的结果
     * 
     * 可以对数据中的HTML标间和特殊字符进行处理
     */
    function SafeHandle($connect, $data) {
        # 过滤字符中的HTML标签
        $data = htmlspecialchars($data);

        # 转义字符串中的特殊字符
        $data = mysqli_real_escape_string($connect, $data);

        return $data;
    }

    /**
     * InitDB - 初始化数据库
     * @dbinfo: 数据库信息数组
     * 
     * @return: 返回数据库连接
     * 
     * dbinfo['host']: 服务器
     * dbinfo['user']: 用户名
     * dbinfo['pwd']: 登录密码
     * dbinfo['db']: 连接的数据库
     * dbinfo['table']: 使用的表格
     */
    function InitDB($dbinfo) {
        $echoinfo = array(
            'code'=>0,
            'message'=>"",
            'data'=>"",
        );
        # 连接数据库
        $connect = mysqli_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pwd'], $dbinfo['db']);
        if (!$connect) {
            $echoinfo['code'] = 200;
            $echoinfo['message'] = "数据库连接失败!".mysqli_connect_error().'，请重试!';
            $echoinfo['data'] = "";
            GiveEcho($echoinfo, null);
        }
        
        # 设置编码
        if(!mysqli_query($connect, "set names utf8")) {
            $echoinfo['code'] = 201;
            $echoinfo['message'] = "设置数据库编码失败:".mysqli_error($connect);
            $echoinfo['data'] = "";
            GiveEcho($echoinfo, $connect);
        }

        $table = $dbinfo['table'];

        # 如果表不存在就创建一个表
        $sql = "CREATE TABLE IF NOT EXISTS ".$table." (
            id INT(6) UNSIGNED AUTO_INCREMENT,
            student_number VARCHAR(13) NOT NULL,
            name VARCHAR(18) NOT NULL,
            sex VARCHAR(3) NOT NULL,
            college VARCHAR(128) NOT NULL,
            major VARCHAR(128) NOT NULL,
            grade VARCHAR(4) NOT NULL,
            phone_number VARCHAR(11) NOT NULL,
            other VARCHAR(255) NOT NULL,
            PRIMARY KEY (id), 
            UNIQUE (student_number)       
        )";

        if(!mysqli_query($connect, $sql)) {
            $echoinfo['code'] = 202;
            $echoinfo['message'] = "创建表格失败:".mysqli_error($connect);
            $echoinfo['data'] = "";
            GiveEcho($echoinfo, $connect);
        }
        return $connect;
    }
    
    # 获取用户数据
    /*$userinfo = array(
        'student_number'=>'2018060916025',
        'name'=>'胡自成',
        'sex'=>'男',
        'college'=>'新闻与传媒学院',
        'major'=>'网络与新媒体',
        'grade'=>'2018',
        'phone_number'=>'13609493580',
        'other'=>'喜欢唱跳RAP篮球'
    );*/
    
    $userinfo = array(
        'student_number'=>$_POST['student_number'],
        'name'=>$_POST['name'],
        'sex'=>$_POST['sex'],
        'college'=>$_POST['college'],
        'major'=>$_POST['major'],
        'grade'=>$_POST['grade'],
        'phone_number'=>$_POST['phone_number'],
        'other'=>$_POST['other']
    );

    # 数据库信息
    $dbinfo = array(
        'host'=>'localhost',
        'user'=>'root',
        'pwd'=>'1234',
        'db'=>'school',
        'table'=>'students',
    );

    $echoinfo = array(
        'code'=>0,
        'message'=>"",
        'data'=>"",
    );

    # ----初始化并连接数据库----
    $connect = InitDB($dbinfo);

    # ----打印登记信息----
    if ($debug) {
        echo ">>>原始登记信息：".$changeline;
        foreach ($userinfo as $key=>$value) {
            echo "key:".$key.'=>value:'.$value.$changeline;
        }
        echo $changeline;
        
    }

    # ----对数据进行验证----

    # ----对长度进行判断----
    $len = strlen($userinfo['name']);
    if ($len == 0 || $len > 18) {
        $echoinfo['code'] = 210;
        $echoinfo['message'] = "名字长度太短或太长!";
        $echoinfo['data'] = "";
        GiveEcho($echoinfo, $connect);
    }

    $len = strlen($userinfo['student_number']);
    if ($len != 13) {
        $echoinfo['code'] = 210;
        $echoinfo['message'] = "请输入有效的学号（13位数字）!";
        $echoinfo['data'] =  "输入的学号长度：".$len;
        GiveEcho($echoinfo, $connect);
    }

    $len = strlen($userinfo['grade']);
    if ($len != 4) {
        $echoinfo['code'] = 210;
        $echoinfo['message'] =  "请输入有效的年级（...2018,2019）!";
        $echoinfo['data'] =  "年级长度".$len;
        GiveEcho($echoinfo, $connect);
    }

    $len = strlen($userinfo['phone_number']);
    if ($len != 11) {
        $echoinfo['code'] = 210;
        $echoinfo['message'] =  "请输入有效的电话号码（11位）!";
        $echoinfo['data'] =  "输入的电话号码长度".$len;
        GiveEcho($echoinfo, $connect);
    }

    # ----对内容进行判断----
    # 验证姓名
    $userinfo['name'] = SafeHandle($connect, $userinfo['name']);

    # 验证学号
    $userinfo['student_number'] = SafeHandle($connect, $userinfo['student_number']);
    # 进行数字处理
    $pattern = '#[0-9]{13}#';   # 13个数字
    preg_match($pattern, $userinfo['student_number'], $match);
    
    # 匹配失败
    if (!$match) {
        $echoinfo['code'] = 211;
        $echoinfo['message'] =  "学号必须全是数字";
        $echoinfo['data'] =  "输入的学号是".$userinfo['student_number'];
        GiveEcho($echoinfo, $connect);
    }

    # 验证电话
    $userinfo['phone_number'] = SafeHandle($connect, $userinfo['phone_number']);
    # 进行数字处理
    $pattern = '#1[0-9]{10}#';  # 11位数字
    preg_match($pattern, $userinfo['phone_number'], $match);
    # 匹配失败
    if (!$match) {
        $echoinfo['code'] = 211;
        $echoinfo['message'] =  "电话号码有误";
        $echoinfo['data'] =  "输入的号码是".$userinfo['phone_number'];
        GiveEcho($echoinfo, $connect);
    }

    # 验证性别
    $userinfo['sex'] = SafeHandle($connect, $userinfo['sex']);
    if ($userinfo['sex'] != "男" && $userinfo['sex'] != "女") {
        $echoinfo['code'] = 211;
        $echoinfo['message'] =  "请输入有效的性别（男或女）!";
        $echoinfo['data'] =  "输入的性别是：".$userinfo['sex'];
        GiveEcho($echoinfo, $connect);
    }

    # 验证年级
    $userinfo['grade'] = SafeHandle($connect, $userinfo['grade']);
    # 进行数字处理
    $pattern = '#201[0-9]#'; # 201x
    preg_match($pattern, $userinfo['grade'], $match);
    # 匹配失败
    if (!$match) {
        $echoinfo['code'] = 211;
        $echoinfo['message'] =  "年级有误";
        $echoinfo['data'] =  "输入的年级是". $userinfo['grade'];
        GiveEcho($echoinfo, $connect);
    }

    # 验证学院
    $userinfo['college'] = SafeHandle($connect, $userinfo['college']);

    # 验证专业
    $userinfo['major'] = SafeHandle($connect, $userinfo['major']);

    # 验证其它
    $userinfo['other'] = SafeHandle($connect, $userinfo['other']);

    if ($debug) {
        echo ">>>验证后的信息：".$changeline;
        foreach ($userinfo as $key=>$value) {
            echo "key:".$key.'=>value:'.$value.$changeline;
        }
        echo $changeline;
    }

    # ----数据写入----

    # 如果有数据就更新数据，没有数据就插入数据，根据学号查询
    $sql = "SELECT * FROM ".$dbinfo['table']." WHERE student_number="."'".$userinfo['student_number']."'";
    if ($debug) {
        echo '>>>SQL查询语句:'.$changeline.$sql.$changeline.$changeline;
    }
    
    $result = mysqli_query($connect, $sql);

    # 如果结果中数量大于0，就说明有数据，那么就更新
    if (mysqli_num_rows($result) > 0) {
        if ($debug) {
            # 用户数据以经存在
            echo ">>>查看已存在的信息：".$changeline;
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                foreach ($row as $key=>$value) {
                    echo "key:".$key.'=>value:'.$value.$changeline;
                }
            }
            echo $changeline;
        }

        #echo '准备更新数据：<br/>';
        # 修改信息
        $sql = "UPDATE ".$dbinfo['table'];
        $sql .= " SET name="."'".$userinfo['name']."'";
        $sql .= ",sex="."'".$userinfo['sex']."'";
        $sql .= ",college="."'".$userinfo['college']."'";
        $sql .= ",major="."'".$userinfo['major']."'";
        $sql .= ",grade="."'".$userinfo['grade']."'";
        $sql .= ",phone_number="."'".$userinfo['phone_number']."'";
        $sql .= ",other="."'".$userinfo['other']."'";
        $sql .= " WHERE student_number="."'".$userinfo['student_number']."'";
        
        if ($debug) {
            echo '>>>SQL更新语句:'.$changeline.$sql.$changeline.$changeline;;
        }
        if (mysqli_query($connect, $sql)) {
            $echoinfo['code'] = 100;
            $echoinfo['message'] = "更新数据成功！";
            $echoinfo['data'] = $userinfo;
            GiveEcho($echoinfo, $connect);
        } else {
            $echoinfo['code'] = 203;
            $echoinfo['message'] = "更新数据失败！";
            $echoinfo['data'] = mysqli_error($connect);
            GiveEcho($echoinfo, $connect);
        }
    } else {
        # 插入新数据
        $sql = "INSERT IGNORE INTO ".$dbinfo['table'].
        "(student_number, name, sex, college, major, grade, phone_number, other)".
        "VALUES"."
        ('{$userinfo['student_number']}',
        '{$userinfo['name']}',
        '{$userinfo['sex']}',
        '{$userinfo['college']}',
        '{$userinfo['major']}',
        '{$userinfo['grade']}',
        '{$userinfo['phone_number']}',
        '{$userinfo['other']}'
        )";

        if ($debug) {
            echo '>>>SQL插入语句:'.$changeline.$sql.$changeline.$changeline;
        }
        if (mysqli_query($connect, $sql)) {
            $echoinfo['code'] = 101;
            $echoinfo['message'] = "插入数据成功！";
            $echoinfo['data'] = $userinfo;
            GiveEcho($echoinfo, $connect);
        } else {
            $echoinfo['code'] = 204;
            $echoinfo['message'] = "插入数据失败！";
            $echoinfo['data'] = mysqli_error($connect);
            GiveEcho($echoinfo, $connect);
        }
    }
?>