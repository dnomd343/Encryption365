<?php

class Output {
    private static $colorEnum = array(
        'red' => '[31m',
        'green' => '[32m',
        'yellow' => '[33m',
        'blue' => '[34m',
        'purple' => '[35m',
        'sky-blue' => '[36m',
    );

    public static function str($content, $color = '') { // 输出字符串
        if (!isset(self::$colorEnum[$color])) {
            echo $content;
            return;
        }
        echo "\033" . self::$colorEnum[$color] . $content . "\033" . "[0m";
    }

    public static function line($content, $color = '') { // 输出一行
        self::str($content . PHP_EOL, $color);
    }
}

class Fake {
    private static function randLocation() { // 生成虚假地址及编码
        $gb2260 = Storage::getGB2260();
        $subData = array();
        foreach ($gb2260 as $code => $content) {
            if (substr($code, 2, 2) === '00') { continue; }
            if (substr($code, 4, 2) === '00') { continue; }
            $subData[$code] = $content;
        }
        $code = array_rand($subData);
        return array(
            'code' => $code,
            'level_1' => $gb2260[substr($code, 0, 2) . '0000'],
            'level_2' => $gb2260[substr($code, 0, 4) . '00'],
            'level_3' => $gb2260[$code],
        );
    }

    private static function randDate() { // 随机生成日期
        $time = rand(3000, 12000) * 24 * 3600;
        return array(
            'date' => date("Ymd", $time),
            'format' => date("Y-m-d", $time)
        );
    }

    public static function randIdNum() { // 生成身份证号
        $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $location = self::randLocation();
        $date = self::randDate();
        $orderNum = rand(100, 999);
        $sex = ($orderNum % 2 !== 0) ? 'male' : 'female';
        $idNum = $location['code'] . $date['date'] . $orderNum;
        $sign = 0;
        for ($i = 0; $i < 17; $i++) {
            $bit = (int)$idNum{$i};
            $sign += $bit * $wi[$i];
        }
        unset($location['code']);
        return array(
            'idNum' => $idNum . $ai[$sign % 11],
            'location' => $location,
            'date' => $date['format'],
            'sex' => $sex
        );
    }

    public static function randPhoneNum() { // 随机电话号码
        $prefix = array(
            130,131,132,133,134,135,136,137,138,139,
            144,147,
            150,151,152,153,155,156,157,158,159,
            176,177,178,
            180,181,182,183,184,185,186,187,188,189,
        );
        return $prefix[array_rand($prefix)] . rand(10000000, 99999999);
    }

    public static function randName($male = true) { // 随机姓名
        $firstname = array(
            '赵','钱','孙','李','周','吴','郑','王','冯','陈','褚','蒋','沈','韩','杨','朱','秦','姬','许','何','吕','张','孔','曹',
            '戚','谢','邹','喻','章','苏','潘','葛','范','彭','鲁','韦','马','方','任','袁','柳','史','唐','姜','薛','雷','贺','陶',
            '汤','殷','罗','毕','郝','常','傅','齐','顾','孟','黄','萧','尹','姚','邵','汪','祁','毛','米','戴','宋','庞','霍','倪',
            '纪','项','祝','董','梁','杜','阮','季','贾','江','童','郭','梅','卢','林','钟','徐','邱','骆','夏','蔡','田','胡','凌',
        );
        $lastname_male = array(
            '伟','刚','勇','毅','俊','峰','强','军','平','文','辉','力','明','永','健','志','兴','良','海','山','波','贵','福','龙',
            '元','全','国','胜','学','祥','才','武','新','清','彬','富','顺','信','子','杰','涛','成','康','光','天','达','中','茂',
            '和','彪','博','诚','震','振','壮','思','豪','邦','承','绍','松','善','磊','民','哲','江','超','浩','亮','政','瀚','行',
            '翰','朗','伯','宏','鸣','斌','栋','维','启','克','伦','翔','旭','鹏','泽','晨','辰','士','建','家','致','树','德','坚',
        );
        $lastname_female = array(
            '楠','榕','风','航','弘','秀','娟','英','慧','巧','美','娜','静','淑','珠','翠','雅','芝','玉','萍','红','娥','玲','芬',
            '芳','燕','彩','春','兰','凤','洁','梅','琳','素','云','莲','雪','霞','香','月','莺','媛','艳','佳','嘉','琼','珍','莉',
            '璐','晶','妍','茜','秋','珊','莎','黛','倩','婷','姣','婉','娴','瑾','颖','瑶','怡','婵','纨','仪','荷','丹','蓉','若',
            '琴','蕊','薇','菁','梦','馨','瑗','韵','园','咏','卿','澜','纯','毓','悦','昭','冰','琬','羽','希','欣','滢','馥','贞',
        );
        $lastname = ($male) ? $lastname_male : $lastname_female;
        $name = $lastname[array_rand($lastname)];
        if (rand(1, 6) > 1) {
            $name .= $lastname[array_rand($lastname)];
        }
        return $firstname[array_rand($firstname)] . $name;
    }
}

class Storage {
    private static $workDir = '/etc/encryption365';

    public static function getGB2260() { // 读取GB2260数据
        return json_decode(file_get_contents(self::$workDir . '/GB2260.json'), true);
    }

    public static function setClientInfo($email, $clientId, $token) { // 客户端凭证写入到本地文件
        $content = 'account = ' . $email . PHP_EOL;
        $content .= 'client_id = ' . $clientId . PHP_EOL;
        $content .= 'access_token = ' . $token . PHP_EOL;
        file_put_contents(self::$workDir . '/client.conf', $content);
    }

    public static function getClientInfo() { // 本地文件读取客户端凭证
        $file = self::$workDir . '/client.conf';
        if (!is_file($file)) {
            file_put_contents($file, '');
            return array();
        }
        $raw = explode(PHP_EOL, file_get_contents($file));
        $info = array();
        foreach ($raw as $row) {
            $row = explode('=', $row);
            if (count($row) !== 2) { continue; }
            $info[trim($row[0])] = trim($row[1]);
        }
        return $info;
    }

    public static function setValidation($content) { // 设置验证内容
        file_put_contents(self::$workDir . '/validation.txt', $content);
    }

    public static function delValidation() { // 删除验证文件
        unlink(self::$workDir . '/validation.txt');
    }

    public static function getHostList() { // 获取站点列表
        $file = self::$workDir . '/site.json';
        if (!is_file($file)) {
            file_put_contents($file, '[]');
            return array();
        }
        return json_decode(file_get_contents($file), true);
    }

    public static function setHostList($hosts) { // 写入站点列表
        file_put_contents(self::$workDir . '/site.json', json_encode($hosts));
    }

    public static function addHost($host) { // 新增一个站点
        self::setHostList(array_merge(
            self::getHostList(), array($host)
        ));
    }

    public static function delHost($host) { // 删除一个站点
        if (!self::isHost($host)) { return; }
        $list = array_flip(self::getHostList());
        unset($list[$host]);
        self::setHostList(array_values(array_flip($list)));
    }

    public static function isHost($host) { // 检查站点是否存在
        return in_array($host, self::getHostList());
    }

    private static function setHostConfig($host, $file, $content) { // 写入站点文件
        $dir = self::$workDir . '/' . $host;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents(self::$workDir . '/' . $host . '/' . $file, $content);
    }

    private static function getHostConfig($host, $file) { // 读取站点文件
        return file_get_contents(self::$workDir . '/' . $host . '/' . $file);
    }

    public static function setPrivkey($host, $content) { // 存储站点私钥
        self::setHostConfig($host, 'private.key', $content);
    }

    public static function getPrivkey($host) { // 读取站点私钥
        return self::getHostConfig($host, 'private.key');
    }

    public static function setCsr($host, $content) { // 存储站点CSR记录
        self::setHostConfig($host, 'csr.dat', $content);
    }

    public static function getCsr($host) { // 读取站点CSR记录
        return self::getHostConfig($host, 'csr.dat');
    }

    public static function setCert($host, $content) { // 储存站点证书文件
        self::setHostConfig($host, 'cert.crt', $content);
    }

    public static function getCert($host) { // 读取站点证书文件
        return self::getHostConfig($host, 'cert.crt');
    }

    public static function setCaCert($host, $content) { // 存储站点CA证书
        self::setHostConfig($host, 'ca.crt', $content);
    }

    public static function getCaCert($host) { // 读取站点CA证书
        return self::getHostConfig($host, 'ca.crt');
    }

    public static function setInfo($host, $info) { // 记录站点信息
        self::setHostConfig($host, 'info.json', json_encode($info));
    }

    public static function getInfo($host) { // 读取站点信息
        return json_decode(self::getHostConfig($host, 'info.json'), true);
    }
}

class Encryption365 {
    private static $version = '1.3.1';
    private static $apiEntry = 'https://encrypt365.trustocean.com';

    private function callApi(string $uri, array $params) { // Encrypt365 API接口
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::$apiEntry . $uri);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($curl, CURLOPT_USERAGENT, 'Encryption365-Client/' . self::$version . ';BaotaPanel-LinuxVersion');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $callResult = curl_exec($curl);
        if (curl_error($curl)) {
            die('Curl Error: ' . curl_error($curl));
        }
        curl_close($curl);
        $result = json_decode($callResult, true);
        if(isset($result['status']) && $result['status'] === 'error') {
            return array(
                'status' => 'error',
                'message' => $result['message']
            );
        }
        return $result;
    }

    private static function getClientInfo() { // 获取客户端ID和token
        $info = Storage::getClientInfo();
        if (!isset($info['client_id'])) {
            Output::line('Fail to get client ID, you must login first.', 'red');
            die();
        }
        if (!isset($info['access_token'])) {
            Output::line('Fail to get access token, you must login first.', 'red');
            die();
        }
        return array(
            'client_id' => $info['client_id'],
            'access_token' => $info['access_token']
        );
    }

    public static function getLatest() { // 获取最新版本
        return self::callApi('/client/version', array());
    }

    public static function authcodeSend($email) { // 发送注册验证码
        return self::callApi('/account/register/authcode', array(
            'username' => $email
        ));
    }

    public static function accountRegister($info) { // 注册账号
        return self::callApi('/account/register', array(
            'username' => $info['email'],
            'password' => $info['password'],
            'authcode' => $info['authcode'],
            'realName' => $info['realName'],
            'idcardNumber' => $info['idcardNumber'],
            'phoneNumber' => $info['phoneNumber'],
            'country' => $info['country'],
            'companyname' => $info['companyName']
        ));
    }

    public static function clientLogin($username, $password) { // 客户端登录
        return self::callApi("/client/create", array(
            'username' => $username,
            'password' => $password,
            'servername' => 'example.com',
        ));
    }

    public static function getAccountDetail() { // 获取账户信息
        return self::callApi('/account/details', self::getClientInfo());
    }

    public static function getProducts() { // 获取可购买产品
        return self::callApi('/account/products', self::getClientInfo())['products'];
    }

    public static function certCreate($productId, $csrCode, $domains) { // 创建证书
        return self::callApi('/cert/create', self::getClientInfo() + array(
            'pid' => $productId,
            'period' => 'quarterly',
            'csr_code' => $csrCode,
            'domains' => implode(',', $domains),
            'renew' => false,
            'old_vendor_id' => -1,
        ));
    }

    public static function certReValidation($vendorId) { // 重新执行域名验证
        return self::callApi('/cert/challenge', self::getClientInfo() + array(
            'trustocean_id' => $vendorId
        ));
    }

    public static function certDetails($vendorId) { // 查询证书详细信息
        return self::callApi('/cert/details', self::getClientInfo() + array(
            'trustocean_id' => $vendorId
        ));
    }

    public static function certRenew($productId, $csrCode, $domains, $oldVendorId) { // 更新证书
        return self::callApi('/cert/create', self::getClientInfo() + array(
            'pid' => $productId,
            'period' => 'quarterly',
            'csr_code' => $csrCode,
            'domains' => implode(',', $domains),
            'renew' => true,
            'old_vendor_id' => $oldVendorId,
        ));
    }

    public static function certReissue($vendorId, $csrCode, $domains) { // 重签SSL证书
        return self::callApi('/cert/reissue', self::getClientInfo() + array(
            'trustocean_id' => $vendorId,
            'csr_code' => $csrCode,
            'domains' => implode(',', $domains)
        ));
    }
}

class Certificate {    
    public static function generateKeyPair($commonName, $isEcc = false) { // 生成密钥对
        if (filter_var($commonName, FILTER_VALIDATE_IP)) { // 若为IP地址
            $commonName = 'common-name-for-public-ip-address.com';
        }
        $subject = array(
            "commonName" => $commonName,
            "organizationName" => "Encryption365 SSL Security",
            "organizationalUnitName" => "Encryption365 SSL Security",
            "localityName" => "Xian",
            "stateOrProvinceName" => "Shaanxi",
            "countryName" => "CN",
        );
        if (!$isEcc) { // RSA证书
            $private_key = openssl_pkey_new(array(
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
                'private_key_bits' => 2048,
                'config' => __DIR__ . '/openssl.cnf'
            ));
            $csr_resource = openssl_csr_new($subject, $private_key, array(
                'digest_alg' => 'sha256',
                'config' => __DIR__ . '/openssl.cnf'
            ));
        } else { // ECC证书
            $private_key = openssl_pkey_new(array(
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => 'prime256v1',
                'config' => __DIR__.'/openssl.cnf'
            ));
            $csr_resource = openssl_csr_new($subject, $private_key, array(
                'digest_alg' => 'sha384',
                'config' => __DIR__ . '/openssl.cnf'
            ));
        }
        openssl_csr_export($csr_resource, $csr_string);
        openssl_pkey_export($private_key, $private_key_string, null, array(
            'config' => __DIR__ . '/openssl.cnf'
        ));
        return array(
            'csr_code' => $csr_string,
            'key_code' => $private_key_string
        );
    }

    public static function preCheck($domains) { // 预先检查http验证是否正常
        $randStr = md5(uniqid(microtime(true), true));
        Storage::setValidation($randStr);
        $result = self::checkValidation($domains, $randStr);
        Storage::delValidation();
        return $result;
    }

    public static function checkValidation($domains, $expect) { // 检查http验证是否正常
        foreach ($domains as $domain) {
            $content = file_get_contents('http://' . $domain . '/.well-known/pki-validation/check');
            if ($content !== $expect) { return false; }
        }
        return true;
    }

    private static function waitIssued($vendorId) { // 等待证书签发
        $maxTime = 60;
        $interval = 30;
        for ($i = 0; $i < $maxTime; $i++) {
            Output::line('Let\'s wait ' . $interval . ' seconds and query certificate status...');
            sleep($interval);
            $detail = Encryption365::certDetails($vendorId);
            if ($detail['result'] !== "success") {
                Output::str('Fail to get certificate details => ');
                Output::line($detail['message'], 'red');
                break;
            }
            if ($detail['cert_status'] === 'issued_active') {
                Output::line('Certificate issue success', 'green');
                return $detail;
            } else {
                Output::str('Certificate status: ');
                Output::line($detail['cert_status'], 'yellow');
            }
        }
        return array('result' => 'fail');
    }

    private static function issueCert($host) { // 轮询证书签发并存储
        $info = Storage::getInfo($host);
        $vendorId = $info['vendorId'];
        $domains = $info['domains'];
        $certInfo = self::waitIssued($vendorId);
        if ($certInfo['result'] !== "success") {
            Output::line('Issue certificate time out, you may validate again...', 'red');
            return false;
        }
        self::saveCert($host, $certInfo);
        return true;
    }

    public static function saveCert($host, $certInfo) {
        $info = Storage::getInfo($host);
        $cert = trim($certInfo['cert_code']);
        $caCert = trim($certInfo['ca_code']);
        $startTime = $certInfo['created_at'];
        $endTime = $certInfo['expire_at'];
        $info['status'] = 'issued';
        $info['createTime'] = $startTime;
        $info['expireTime'] = $endTime;
        Storage::setInfo($host, $info);
        Storage::setCert($host, $cert);
        Storage::setCaCert($host, $caCert);
        Output::line('Certificate: ', 'purple');
        echo $cert . PHP_EOL;
        Output::line('CA Certificate: ', 'purple');
        echo $caCert . PHP_EOL;
        Output::str('Create time: ');
        Output::line($startTime, 'sky-blue');
        Output::str('Expire time: ');
        Output::line($endTime, 'sky-blue');
    }

    public static function createCert($productId, $domains, $isEcc = false) { // 创建新的证书订单
        if (!self::preCheck($domains)) {
            Output::line('Inaccessible http://{domain}/.well-known/pki-validation/... as validation.');
            return;
        }
        $host = $domains[0];
        $newCsrKey = self::generateKeyPair($host, $isEcc);
        Storage::setPrivkey($host, $newCsrKey['key_code']);
        Storage::setCsr($host, $newCsrKey['csr_code']);
        $orderRlt = Encryption365::certCreate($productId, $newCsrKey['csr_code'], $domains);
        if ($orderRlt['result'] !== "success") {
            Output::str('Fail to create certificate => ');
            Output::line($orderRlt['message'], 'red');
            return;
        }
        $vendorId = (string)$orderRlt['trustocean_id'];
        foreach ($orderRlt['dcv_info'] as $domain => $dcvInfo) {
            preg_match('/^http:\/\/[\S]*?(\/[\S]+.txt)/', $dcvInfo['http_verifylink'], $match);
            $verifyLink = 'http://{domain}' . $match[1];
            $verifyContent = $dcvInfo['http_filecontent'];
            break;
        }
        Storage::setInfo($host, array(
            'host' => $host,
            'vendorId' => $vendorId,
            'productId' => $productId,
            'domains' => $domains,
            'status' => 'issuing',
            'isEcc' => $isEcc
        )); 
        Output::str('Vendor id: ');
        Output::str($vendorId . PHP_EOL, 'sky-blue');
        Output::str('Verify link: ');
        Output::str($verifyLink . PHP_EOL, 'sky-blue');
        Output::line('Verify content: ');
        Output::str($verifyContent . PHP_EOL, 'sky-blue');
        Storage::setValidation($verifyContent);
        if (self::issueCert($host)) {
            Storage::delValidation();
        }
    }

    public static function reValidate($host) { // 重新发起验证
        $info = Storage::getInfo($host);
        if ($info['status'] === 'issued') {
            Output::line('This site has been successfully issued.');
            return;
        }
        if (!self::preCheck($info['domains'])) {
            Output::line('Inaccessible http://{domain}/.well-known/pki-validation/... as validation.');
            return;
        }
        $status = Encryption365::certReValidation($info['vendorId']);
        if ($status['status'] !== "success") {
            Output::str('Fail to revalidate certificate => ');
            Output::line($status['message'], 'red');
            return;
        }
        if (self::issueCert($host)) {
            Storage::delValidation();
        }
    }

    public static function renewCert($host) { // 更新证书
        $info = Storage::getInfo($host);
        if ($info['status'] !== 'issued') {
            Output::line('This site has never been issued.', 'red');
            return;
        }
        if (!self::preCheck($info['domains'])) {
            Output::line('Inaccessible http://{domain}/.well-known/pki-validation/... as validation.');
            return;
        }
        $newCsrKey = self::generateKeyPair($host, $info['isEcc']);
        Storage::setPrivkey($host, $newCsrKey['key_code']);
        Storage::setCsr($host, $newCsrKey['csr_code']);
        $orderRlt = Encryption365::certRenew($info['productId'], $newCsrKey['csr_code'], $info['domains'], $info['vendorId']);
        if ($orderRlt['result'] !== "success") {
            Output::str('Fail to renew certificate => ');
            Output::line($orderRlt['message'], 'red');
            return;
        }
        $vendorId = (string)$orderRlt['trustocean_id'];
        foreach ($orderRlt['dcv_info'] as $domain => $dcvInfo) {
            preg_match('/^http:\/\/[\S]*?(\/[\S]+.txt)/', $dcvInfo['http_verifylink'], $match);
            $verifyLink = 'http://{domain}' . $match[1];
            $verifyContent = $dcvInfo['http_filecontent'];
            break;
        }
        $info['status'] = 'issuing';
        $info['vendorId'] = $vendorId;
        unset($info['createTime']);
        unset($info['expireTime']);
        Storage::setInfo($host, $info); 
        Output::str('Vendor id: ');
        Output::str($vendorId . PHP_EOL, 'sky-blue');
        Output::str('Verify link: ');
        Output::str($verifyLink . PHP_EOL, 'sky-blue');
        Output::line('Verify content: ');
        Output::str($verifyContent . PHP_EOL, 'sky-blue');
        Storage::setValidation($verifyContent);
        if (self::issueCert($host)) {
            Storage::delValidation();
        }
    }
}

class RegistCtr {
    public static function interact() { // 注册交互
        Output::line('This process help you regist a Trustocean account, all you need is an email address to receive the verification code.');
        Output::str('Email: ', 'yellow');
        $email = trim(fgets(STDIN));
        $result = Encryption365::authcodeSend($email);
        if ($result['result'] !== 'success') {
            Output::str('Fail to send auth code: ');
            Output::str($result['message'], 'red');
            return;
        }
        Output::line('There will be an email sent to ' . $email . ', please note checking.');
        Output::str('Verification code: ', 'yellow');
        $code = trim(fgets(STDIN));
        Output::line('Set a password for your account.');
        Output::str('Password: ', 'yellow');
        $passwd = trim(fgets(STDIN));
        Output::line('Trustocean need your Chinese ID card num, but you can just press ENTER to generate one.');
        Output::str('ID card num: ', 'yellow');
        $idNum = trim(fgets(STDIN));
        if ($idNum === '') {
            $fake = Fake::randIdNum();
            Output::str('Random ID card num: ');
            Output::str($fake['idNum'] . PHP_EOL, 'sky-blue');
            Output::str('Random ID card info: ');
            Output::str(implode(' ', $fake['location']) . ' ' . $fake['date'] . ' ' . $fake['sex'] . PHP_EOL, 'sky-blue');
        }
        Output::line('Please enter your real name, or press ENTER to generate one.');
        Output::str('Name: ', 'yellow');
        $name = trim(fgets(STDIN));
        if ($name === '') {
            $fake['name'] = Fake::randName(($fake['sex'] === 'male'));
            Output::str('Random name: ');
            Output::str($fake['name'] . PHP_EOL, 'sky-blue');
        }
        Output::line('Last step, enter your phone num, or randomly generate one by press ENTER.');
        Output::str('Phone: ', 'yellow');
        $phone = trim(fgets(STDIN));
        if ($phone === '') {
            $fake['phone'] = Fake::randPhoneNum();
            Output::str('Random phone: ');
            Output::str($fake['phone'] . PHP_EOL, 'sky-blue');
        }
        Output::str('It seems that all the information is complete, press ENTER to register your account...');
        fgets(STDIN);
        if (self::regist($email, $passwd, $code, $fake['name'], $fake['idNum'], $fake['phone'])) {
            Output::line('Use "encryption365 login" command to login.');
        }
    }

    private static function regist($email, $passwd, $code, $name, $idNum, $phone) { // 发起注册
        $result = Encryption365::accountRegister(array(
            'email' => $email,
            'password' => $passwd,
            'authcode' => $code,
            'realName' => $name,
            'idcardNumber' => $idNum,
            'phoneNumber' => $phone,
            'country' => 'CN',
            'companyName' => '无'
        ));
        if ($result['result'] !== 'success') {
            Output::str('Fail to regist: ');
            Output::line($result['message'], 'red');
            return false;
        }
        Output::line('Regist success', 'green');
        return true;
    }
}

class LoginCtr {
    public static function entry($params) {
        switch (count($params)) {
            case '0':
                self::interact();
                break;
            case '2':
                self::login($params[0], $params[1]);
                break;
            default:
                Output::line('Unknow params');
        }
    }

    public static function interact() { // 登录交互
        Output::line('This process need a Trustocean account, if you don\'t have it currently, there are two methods:');
        Output::line('1. Manually register at "https://page.trustocean.com/websecurity/welcome"');
        Output::line('2. Use "encryption365 regist" command regist automatically');
        Output::str('Email: ');
        $email = trim(fgets(STDIN));
        Output::str('Password: ');
        $passwd = trim(fgets(STDIN));
        self::login($email, $passwd);
    }

    public static function login($email, $passwd) { // 发起登录
        $result = Encryption365::clientLogin($email, $passwd);
        if ($result['result'] !== 'success') {
            Output::str('Fail to login: ');
            Output::line($result['message'], 'red');
            return;
        }
        Output::line('Login success', 'green');
        Output::line('Account status: ' . $result['status']);
        Output::line('Login time: ' . $result['created_at']);
        Storage::setClientInfo($email, $result['client_id'], $result['access_token']);
    }
}

class ListCtr {
    public static function list() { // 列出所有证书
        $list = Storage::getHostList();
        if (count($list) === 0) {
            Output::line('There are not any host yet.', 'yellow');
            return;
        }
        Output::line("Host\t\tID\tDomains\t\t\tCreate Time\t\tExpire Time");
        foreach ($list as $host) {
            $info = Storage::getInfo($host);
            Output::str($info['host'] . "\t", 'yellow');
            Output::str($info['vendorId'] . "\t", 'sky-blue');
            Output::str(implode('/', $info['domains']) . "\t", 'purple');
            if (isset($info['createTime'])) {
                Output::str($info['createTime'] . "\t", 'blue');
            } else {
                Output::str('-' . "\t\t", 'blue');
            }
            if (isset($info['expireTime'])) {
                Output::str($info['expireTime'] . "\t", 'blue');
            } else {
                Output::str('-' . "\t\t", 'blue');
            }
            Output::line('');
        }
    }
}

class IssueCtr {
    public static function entry($params) {
        if (count($params) === 0) {
            Output::line('You must specify encryption method.', 'red');
            return;
        }
        if ($params[0] === 'ECC') { $isEcc = true; }
        if ($params[0] === 'RSA') { $isEcc = false; }
        if (!isset($isEcc)) {
            Output::line('You must specify RSA or ECC.', 'red');
            return;
        }
        if (count($params) === 1) {
            Output::line('You must specify at least one IP or domain name.', 'red');
            return;
        }
        unset($params[0]);
        $domains = array_values($params);
        foreach ($domains as $host) {
            if (!self::isHost($host)) {
                Output::str($host, 'red');
                Output::line(' is not a legel IP or domain name.');
                return;
            }
        }
        $productId = self::getProductId();
        if (!is_numeric($productId)) {
            Output::line('Illegal product ID.', 'red');
            return;
        }
        Storage::addHost($domains[0]);
        Certificate::createCert($productId, $domains, $isEcc);
    }

    private function isHost($host) { // 判断host是否合法
        preg_match('/^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/', $host, $match);
        if (count($match) !== 0) {
            if (!is_numeric(substr($host, -1))) { return true; }
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }
        return false;
    }

    private static function getProductId() { // 获取免费证书产品ID
        $products = Encryption365::getProducts();
        if (count($products) === 0) {
            Output::line('Can\'t find any available products.', 'red');
            return;
        }
        foreach ($products as $productId => $product) {
            if (strpos($product['title'], '免费') !== false) {
                return (string)$productId;
            }
        }
        Output::line('We\'ve encountered an error while processing your request, you might want to select the free certificate\'s product ID manually.', 'yellow');
        foreach ($products as $productId => $product) {
            Output::str('Product ID: ' . $productId . ' => ', 'sky-blue');
            Output::line($product['title'] . ' ' . $product['class'] . ' ' . $product['useage'] . ' ' . $product['term'], 'sky-blue');
        }
        Output::str('Product ID: ');
        return trim(fgets(STDIN));
    }
}

class ReverifyCtr {
    public static function entry($params) {
        if (count($params) === 0) {
            Output::line('Host must be specified.', 'red');
            return;
        }
        if (count($params) > 1) {
            Output::line('Too many parameters to reverify command.', 'red');
            return;
        }
        Certificate::reValidate($params[0]);
    }
}

class FlashCtr {
    public static function entry($params) {
        if (count($params) === 0) {
            Output::line('Host must be specified.', 'red');
            return;
        }
        if (count($params) > 1) {
            Output::line('Too many parameters to flash certificate.', 'red');
            return;
        }
        $host = $params[0];
        $info = Storage::getInfo($host);
        $detail = Encryption365::certDetails($info['vendorId']);
        if ($detail['result'] !== "success") {
            Output::str('Fail to flash certificate => ');
            Output::line($detail['message'], 'red');
        }
        Output::str('Certificate status: ');
        Output::line($detail['cert_status'], 'sky-blue');
        if ($detail['cert_status'] === 'issued_active') {
            Certificate::saveCert($host, $detail);
        }
    }
}

class RenewCtr {
    public static function entry($params) {
        if (count($params) === 0) {
            Output::line('Host must be specified.', 'red');
            return;
        }
        if (count($params) > 1) {
            Output::line('Too many parameters to reverify command.', 'red');
            return;
        }
        Certificate::renewCert($params[0]);
    }
}

class InstallCtr {
    public static function entry($params) {
        if (count($params) === 0) {
            Output::line('Host must be specified.', 'red');
            return;
        }
        if (count($params) === 1) {
            Output::line('You must provide at least one parameter.', 'red');
            return;
        }
        $host = $params[0];
        unset($params[0]);
        $params = array_values($params);
        foreach ($params as $field) {
            preg_match('/^([a-zA-Z]+)=([\s\S]*)$/', $field, $match);
            if (count($match) !== 3) {
                Output::str($field, 'red');
                Output::line(' is illegal.');
                return;
            }
            $match[1] = strtolower($match[1]);
            if ($match[1] !== 'fullchain' && $match[1] !== 'key' && $match[1] !== 'cert' && $match[1] !== 'ca' && $match[1] !== 'cmd') {
                Output::str($match[1], 'red');
                Output::line(' is not a legal option.');
                return;
            }
            $option[$match[1]] = trim($match[2]);
        }
        if (!Storage::isHost($host)) {
            Output::str($host, 'red');
            Output::line(' is not exist.');
            return;
        }
        $info = Storage::getInfo($host);
        Storage::setInfo($host, array_merge($info, $option));
        self::install($host);
    }

    public static function install($host) { // 安装证书
        $info = Storage::getInfo($host);
        if ($info['status'] !== 'issued') {
            Output::line('This site has never been issued.', 'red');
            return;
        }
        $fields = array('fullchain', 'key', 'cert', 'ca');
        foreach ($fields as $field) {
            if (isset($info[$field]) && $info[$field] !== '') {
                switch ($field) {
                    case 'fullchain':
                        $content = Storage::getCert($host) . PHP_EOL . Storage::getCaCert($host);
                        break;
                    case 'key':
                        $content = Storage::getPrivkey($host);
                        break;
                    case 'cert':
                        $content = Storage::getCert($host);
                        break;
                    case 'ca':
                        $content = Storage::getCaCert($host);
                        break;
                }
                file_put_contents($info[$field], $content);
                if ($field === 'key') {
                    chmod($info[$field], 0600);
                }
            }
        }
        if (isset($info['cmd']) && $info['cmd'] !== '') {
            shell_exec($info['cmd']);
        }
        Output::line('Install OK', 'green');
    }
}

class AutorenewCtr {
    public static function renew() {
        $list = Storage::getHostList();
        foreach ($list as $host) {
            $info = Storage::getInfo($host);
            if ($info['status'] !== 'issued') { continue; }
            $expire = strtotime($info['expireTime']);
            $now = strtotime(date('Y-m-d H:i:s'));
            $remainDays = intval(($expire - $now) / 3600 / 24);
            if ($remainDays >= 10) { continue; }
            Output::str('Renew certificate for ');
            Output::line($host, 'yellow');
            Certificate::renewCert($host);
            InstallCtr::install($host);
        }
    }
}

function noParam($params) { // 命令不含参数情况
    if (count($params) === 0) { return; }
    echo 'Unknow params' . PHP_EOL;
    exit;
}

function showHelp() { // 显示帮助信息
    echo <<<EOF

A tool to automatically apply and manage for Encryption365 free certificate.

Source code repository: https://github.com/dnomd343/Encryption365.git

Usage: encryption365 COMMAND [OPTIONS]

  help => Output this help information.

  regist => Register Trustocean account in command line.

  login => Login your Trustocean account.

  list => List all sites.

  issue METHOD DOMAIN_1 DOMAIN_2 ... => Try to apply for TLS certificate.

    METHOD => ECC or RSA (ECC recommended)

    DOMAIN_x => Domain name or IP address included in the certificate.
                (The first one will be the common name)

  reverify HOST => Re-apply for verification.

  flash HOST => Check the site status and download the certificate again.

  renew HOST => Renew a certificate that is nearly expire.

  install HOST [fullchain=...] [key=...] [cert=...] [ca=...] [cmd=...]
  => Install the TLS certificate.

    fullchain => Full certificate chain.

    key => Private key of certificate.

    cert => Certificate

    ca => CA certificate

    cmd => Running command after installation

  autorenew => Check all sites and update certificates that are about to expire.

Examples:

> encryption365 login dnomd343@163.com {password}

> encryption365 issue ECC 8.210.148.24 dns.343.re

> encryption365 install 8.210.148.24 \
    fullchain=/etc/ssl/certs/8.210.148.24/fullchain.pem \
    key=/etc/ssl/certs/8.210.148.24/privkey.pem \
    cmd="systemctl force-reload nginx"


EOF;
}

function main($argv) { // 脚本入口
    unset($argv[0]);
    if (count($argv) === 0) {
        $argv[] = 'help';
    }
    $command = $argv[1];
    unset($argv[1]);
    $params = array_values($argv);
    switch ($command) {
        case 'help':
            noParam($params);
            showHelp();
            break;
        case 'regist':
            noParam($params);
            RegistCtr::interact();
            break;
        case 'login':
            LoginCtr::entry($params);
            break;
        case 'list':
            noParam($params);
            ListCtr::list();
            break;
        case 'issue':
            IssueCtr::entry($params);
            break;
        case 'reverify':
            ReverifyCtr::entry($params);
            break;
        case 'flash':
            FlashCtr::entry($params);
            break;
        case 'renew':
            RenewCtr::entry($params);
            break;
        case 'install':
            InstallCtr::entry($params);
            break;
        case 'autorenew':
            noParam($params);
            AutorenewCtr::renew();
            break;
        default:
            echo 'Unknow command, please use "encryption365 help" to show the usage.' . PHP_EOL;
    }
}

main($argv);

?>
