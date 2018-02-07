<?php 

class Translation {

    private static $instance = null;
    private static $db;

    protected $enTranslations = [];
    protected $translations = [];
    private $langCode;

    public static function init($db, $langCode) {
        if (!isset(self::$instance)) {
            self::$db = $db;
            self::$instance = new Translation($db, $langCode);
        }
    }

    public function resetLanguage($langCode) {
        if ($langCode !== $this->langCode) {
            self::$instance = new Translation(self::$db, $langCode);
        }
    }

    public static function instance() {
        return self::$instance;
    }

    private function __construct($db, $langCode) {
        $this->langCode = $langCode;

        $results = $db->fetchAll("SELECT id, lang_code, content FROM " . VAX_DB_PREFIX . "translations WHERE lang_code IN ('en', '$this->langCode')");
        foreach ($results as $result) {
            if ($result['lang_code'] == $langCode) {
                $this->translations[$result['id']] = $result['content'];
            }
            if ($result['lang_code'] == 'en') {
                $this->enTranslations[$result['id']] = $result['content'];
            }
        }
    }

    public function show($id, $params = []) {

        if (isset($this->translations[$id])) {
            $text = $this->translations[$id];
        } else {
            if (isset($this->enTranslations[$id])) {
                $text = $this->enTranslations[$id];
            } else {
                throw new Exception("The translation '{$id}' does not exist.");
            }
        }

        $params = array_merge($params, ['<BOLD_START&&>' => '<b>', '<BOLD_END&&>' => '</b>', '<WEB_NAME&&>' => VAX_NAME]);

        if (!empty($params)) {
            $text = str_replace(array_keys($params), array_values($params), $text);
        }

        if (in_array($this->langCode, ['ca', 'fr'])) {
            
        }

        return $this->articleConsistency($text);
    }

    public function echo($id, $params = []) {
        echo $this->show($id, $params);
    }

    private function articleConsistency($text) {
        $article_d = '<ARTICLE_D&&>';
        $article_l = '<ARTICLE_L&&>';
        $article_au = '<ARTICLE_AU&&>';

        $replace_dv = '';
        $replace_dc = '';
        $replace_lv = '';
        $replace_lc = '';
        $replace_au = '';

        $vowels = ['a', 'e', 'i', 'o', 'u', 'à', 'è', 'é', 'í', 'ò', 'ó', 'ú',
                   'A', 'E', 'I', 'O', 'U', 'À', 'È', 'É', 'Í', 'Ò', 'Ó', 'Ú'];

        switch ($this->langCode) {
            case 'fr':
                $replace_dv = "d'";
                $replace_dc = 'de ';

                $replace_lv = "l'";
                $replace_lc = 'le ';

                $replace_au = 'au';
                break;

            case 'ca':
                $replace_dv = "d'";
                $replace_dc = 'de ';

                $replace_lv = "l'";
                $replace_lc = 'el ';
                break;
        }

        while ($pos = strpos($text, $article_d)) {
            $next_char = strtolower(substr($text, $pos + strlen($article_d), 1));
            if (in_array($next_char, $vowels)) {
                $text = preg_replace("/".$article_d."/", $replace_dv, $text, 1);
            } else {
                $text = preg_replace("/".$article_d."/", $replace_dc, $text, 1);
            }
        }

        while ($pos = strpos($text, $article_l)) {
            $next_char = strtolower(substr($text, $pos + strlen($article_l), 1));
            if (in_array($next_char, $vowels)) {
                $text = preg_replace("/".$article_l."/", $replace_lv, $text, 1);
            } else {
                $text = preg_replace("/".$article_l."/", $replace_lc, $text, 1);
            }
        }

        if ($pos = strpos($text, $article_au)) {
            $text = str_replace($article_au, $replace_au, $text);
        }

        return $text;
    }

}