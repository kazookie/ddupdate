#!/usr/bin/php

<?php

if(isRoot()) {
    $client = new Client();
    $client->load('/etc/ddupdate.json');
    $client->setIP();
    $client->post();
}
else {
    $warning = "Can't execute in the general user.\n";
    $warning .= "Please run as root.\n";
    print($warning);
}

/**
 * 　rootユーザが実行しているか確認する
 */
function isRoot() {
    $user = exec('whoami');
    return ($user == 'root') ? true : false;
}

class Client
{
    // DDNS
    private $ddns = "http://dyn.value-domain.com/cgi-bin/dyn.fcg?";
    // IP検出URL
    private $detect = 'http://dyn.value-domain.com/cgi-bin/dyn.fcg?ip';
    // IP
    private $ip = '';
    // ドメイン
    private $domain = '';
    // パスワード
    private $password = '';
    // ホスト
    private $host = [];
    // タイムゾーン
    private $timezone = 'Asia/Tokyo';
    // ログファイル
    private $log = '/var/log/ddupdate.log';

    /**
     * 設定ファイルを読込む
     *
     * @param stirng
     */
    public function load($path)
    {
        // JSON
        $json = file_get_contents($path);
        $setting = json_decode($json);

        $this->domain = $setting->domain;
        $this->password = $setting->password;
        foreach($setting->host as $host) {
            array_push($this->host, $host);
        }
    }

    /**
     * 現在のIPを取得する
     *
     * @return string
     */
    public function getIP()
    {
        $ip = file_get_contents($this->detect);
        return $ip;
    }

    /**
     * IPを設定する
     *
     * @param string
     */
    public function setIP($ip = null)
    {
        $this->ip = isset($ip) ? $ip : $this->getIP();
    }

    /**
     * 設定をポストする
     *
     */
    public function post()
    {
        // IPの変化が無い場合、更新しない
        if(!$this->changed()) return;

        $data = array(
            "d" => $this->domain,
            "p" => $this->password,
            "i" => $this->ip,
        );

        foreach($this->host as $host) {
            $data['h'] = $host;
            $content = file_get_contents($this->ddns . http_build_query($data));
        }

        print($this->ip . "\n");

        $this->putlog();
    }

    /**
     * IPの変化をチェックする
     *
     * @return bool
     */
    public function changed()
    {
        // ログファイルが無い場合
        if(!file_exists($this->log)) {
            return true;
        }

        // ログファイルを読み込む
        $logs = file($this->log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // IPを取出す
        $logs = array_reverse($logs);
        $ip = explode("\t", $logs[0])[0];

        if($ip == $this->ip) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * ログを書く
     */
    public function putlog()
    {
        date_default_timezone_set($this->timezone);
        $fp = fopen($this->log, 'a');
        $msg = $this->ip . "\t" . date('Y-m-d h:i:s') . "\n";
        fwrite($fp, $msg);
        fclose($fp);
    }
}
