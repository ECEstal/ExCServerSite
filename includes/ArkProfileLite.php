<?php
class ArkProfileLite {
    public $playerName = null;
    public $level = null;
    public $tribeName = null;
    public $steamId = null;

    public function __construct($filename) {
        if (!file_exists($filename)) return;

        $f = fopen($filename, 'rb');
        if (!$f) return;

        $raw = fread($f, filesize($filename));
        fclose($f);

        // Search for PlayerName, TribeName, etc. as ASCII
        if (preg_match('/PlayerName.{0,20}([A-Za-z0-9_\\- ]{3,64})/s', $raw, $m)) {
            $this->playerName = trim($m[1]);
        }
        if (preg_match('/TribeName.{0,20}([A-Za-z0-9_\\- ]{3,64})/s', $raw, $m)) {
            $this->tribeName = trim($m[1]);
        }
        if (preg_match('/SteamID.{0,20}([0-9]{15,20})/', $raw, $m)) {
            $this->steamId = trim($m[1]);
        } elseif (preg_match('/([0-9]{17})\\.arkprofile$/', $filename, $m)) {
            $this->steamId = $m[1];
        }
        if (preg_match('/CharacterLevel.{0,8}([\\x00-\\xff]{4})/s', $raw, $m)) {
            // 4 bytes, little endian integer
            $this->level = unpack('V', $m[1])[1];
        }
    }
}
?>
