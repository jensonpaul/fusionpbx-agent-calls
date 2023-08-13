<?php

if (!class_exists('ISO639')) {
    class ISO639
    {
        protected $languages = array(
			array('ACH', 'Acholi', 'OTPLS4', 1),
			array('AFG', 'Afghan', 'OTPLS4', 2),
			array('AFR', 'Afrikaans', 'OTPLS4', 3),
			array('AKA', 'Akan', 'OTPLS4', 4),
			array('ALB', 'Albanian', 'OTPLS3', 5),
			array('ASL', 'American Sign Language', 'OTPLS1', 6),
			array('AMH', 'Amharic', 'OTPLS3', 7),
			array('ARA', 'Arabic', 'OTPLS2', 8),
			array('ARM', 'Armenian', 'OTPLS4', 9),
			array('ASH', 'Ashanti', 'OTPLS4', 10),
			array('ASS', 'Assyrian', 'OTPLS4', 11),
			array('AZE', 'Azerbaijani', 'OTPLS4', 12),
			array('BAH', 'Bahasa', 'OTPLS4', 13),
			array('BAL', 'Baluchi', 'OTPLS4', 14),
			array('BAM', 'Bambara', 'OTPLS4', 15),
			array('BAR', 'Bari', 'OTPLS4', 16),
			array('BEI', 'Beijing', 'OTPLS4', 17),
			array('BEN', 'Bengali', 'OTPLS4', 18),
			array('BHO', 'Bhojpuri', 'OTPLS4', 19),
			array('BHU', 'Bhutanese', 'OTPLS4', 20),
			array('BIH', 'Bihari', 'OTPLS4', 21),
			array('BOR', 'Borana', 'OTPLS4', 22),
			array('BOS', 'Bosnian', 'OTPLS3', 23),
			array('BUL', 'Bulgarian', 'OTPLS4', 24),
			array('BUR', 'Burmese', 'OTPLS4', 25),
			array('BEL', 'Belarusian', 'OTPLS4', 26),
			array('CAM', 'Cambodian', 'OTPLS4', 27),
			array('CAN', 'Cantonese', 'OTPLS3', 28),
			array('CAP', 'Cape Verdean', 'OTPLS4', 29),
			array('CAT', 'Catalan', 'OTPLS4', 30),
			array('CEB', 'Cebuhano', 'OTPLS4', 31),
			array('CHA', 'Chaldean', 'OTPLS4', 32),
			array('CHC', 'Chao Chow', 'OTPLS4', 33),
			array('CHI', 'Chin', 'OTPLS4', 34),
			array('CHU', 'Chuukese', 'OTPLS4', 35),
			array('CRK', 'Creek', 'OTPLS4', 36),
			array('CRE', 'Creole', 'OTPLS4', 37),
			array('CRI', 'Crioulo', 'OTPLS4', 38),
			array('CRO', 'Croatian', 'OTPLS3', 39),
			array('CZE', 'Czech', 'OTPLS4', 40),
			array('DAN', 'Danish', 'OTPLS4', 41),
			array('DAR', 'Dari', 'OTPLS4', 42),
			array('DIN', 'Dinka', 'OTPLS3', 43),
			array('DUT', 'Dutch', 'OTPLS4', 44),
			array('EBO', 'Ebon', 'OTPLS4', 45),
			array('EDO', 'Edo', 'OTPLS4', 46),
			array('ERI', 'Eritrean', 'OTPLS4', 47),
			array('EST', 'Estonian', 'OTPLS4', 48),
			array('ETH', 'Ethiopian', 'OTPLS4', 49),
			array('EWE', 'Ewe', 'OTPLS4', 50),
			array('FAN', 'Fanti', 'OTPLS4', 51),
			array('FAR', 'Farsi', 'OTPLS3', 52),
			array('FIJ', 'Fijian', 'OTPLS4', 53),
			array('FIN', 'Finnish', 'OTPLS4', 54),
			array('FLE', 'Flemish', 'OTPLS4', 55),
			array('FOO', 'Foochow', 'OTPLS4', 56),
			array('FOK', 'Foukien', 'OTPLS4', 57),
			array('FOU', 'Foulany', 'OTPLS4', 58),
			array('FRE', 'French', 'OTPLS3', 59),
			array('FRC', 'French Canadian', 'OTPLS4', 60),
			array('FUK', 'Fukienese', 'OTPLS4', 61),
			array('FUL', 'Fulani', 'OTPLS4', 62),
			array('GA', 'Ga', 'OTPLS4', 63),
			array('GEO', 'Georgian', 'OTPLS4', 64),
			array('GER', 'German', 'OTPLS3', 65),
			array('GRB', 'Grebo', 'OTPLS4', 66),
			array('GRE', 'Greek', 'OTPLS4', 67),
			array('GUA', 'Guarani', 'OTPLS4', 68),
			array('GUJ', 'Gujarati', 'OTPLS4', 69),
			array('HAI', 'Haitian Creole', 'OTPLS4', 70),
			array('HAK', 'Hakka', 'OTPLS4', 71),
			array('HAU', 'Hausa', 'OTPLS4', 72),
			array('HEB', 'Hebrew', 'OTPLS4', 73),
			array('HIN', 'Hindi', 'OTPLS3', 74),
			array('HIS', 'Hindustani', 'OTPLS4', 75),
			array('HMO', 'Hmong', 'OTPLS4', 76),
			array('HOK', 'Hokkien', 'OTPLS4', 77),
			array('HUN', 'Hungarian', 'OTPLS4', 78),
			array('IBO', 'Ibo', 'OTPLS4', 79),
			array('IGB', 'Igbo', 'OTPLS4', 80),
			array('ILC', 'Ilocano', 'OTPLS4', 81),
			array('ILO', 'Ilonggo', 'OTPLS4', 82),
			array('IND', 'Indonesian', 'OTPLS4', 83),
			array('ITA', 'Italian', 'OTPLS3', 84),
			array('JAL', 'Jalam', 'OTPLS4', 85),
			array('JAM', 'Jamaican English Creole', 'OTPLS4', 86),
			array('JAP', 'Japanese', 'OTPLS3', 87),
			array('JOL', 'Jollas', 'OTPLS4', 88),
			array('JUL', 'Jula', 'OTPLS4', 89),
			array('KAI', 'Kachchi', 'OTPLS4', 90),
			array('KAC', 'Kachin', 'OTPLS4', 91),
			array('KAJ', 'Kanjobal', 'OTPLS4', 92),
			array('KAN', 'Kannada', 'OTPLS4', 93),
			array('KAR', 'Karen', 'OTPLS4', 94),
			array('KRN', 'Karenni/Kayah', 'OTPLS4', 95),
			array('KAZ', 'Kazakh', 'OTPLS4', 96),
			array('KHM', 'Khmer', 'OTPLS4', 97),
			array('KIK', 'Kikuyu', 'OTPLS4', 98),
			array('KIN', 'Kinya/Rwanda', 'OTPLS4', 99),
			array('KTI', 'Kirati', 'OTPLS4', 100),
			array('KIR', 'Kirundi', 'OTPLS4', 101),
			array('KIZ', 'Kizigua', 'OTPLS4', 102),
			array('KON', 'Kongo', 'OTPLS4', 103),
			array('KOR', 'Korean', 'OTPLS3', 104),
			array('KRA', 'Krahn', 'OTPLS4', 105),
			array('KRI', 'Krio', 'OTPLS4', 106),
			array('KUN', 'Kunama', 'OTPLS4', 107),
			array('KUB', 'Kurdish-Behdini', 'OTPLS4', 108),
			array('KUR', 'Kurdish-Sorani', 'OTPLS2', 109),
			array('KUM', 'Kurmanji', 'OTPLS4', 110),
			array('KYR', 'Kyrgyz', 'OTPLS4', 111),
			array('LAN', 'Lanvhou', 'OTPLS4', 112),
			array('LAO', 'Laotian', 'OTPLS2', 113),
			array('LAT', 'Latvian', 'OTPLS4', 114),
			array('LIA', 'Lingala', 'OTPLS4', 115),
			array('LIN', 'Lingali', 'OTPLS4', 116),
			array('LIT', 'Lithuanian', 'OTPLS4', 117),
			array('LUG', 'Luganda', 'OTPLS4', 118),
			array('LUN', 'Lunda', 'OTPLS4', 119),
			array('LUO', 'Luo', 'OTPLS4', 120),
			array('MMY', 'Maay Maay', 'OTPLS4', 121),
			array('MAA', 'Maay Somali', 'OTPLS4', 122),
			array('MAC', 'Macedonian', 'OTPLS4', 123),
			array('MAD', 'Madi', 'OTPLS4', 124),
			array('MAG', 'Malagasy', 'OTPLS4', 125),
			array('MAL', 'Malay', 'OTPLS4', 126),
			array('MLM', 'Malayalam', 'OTPLS4', 127),
			array('MLK', 'Malinke', 'OTPLS4', 128),
			array('MAM', 'Mam', 'OTPLS4', 129),
			array('MAN', 'Mandarin', 'OTPLS3', 130),
			array('MND', 'Mandingo', 'OTPLS4', 131),
			array('MDK', 'Mandinka', 'OTPLS4', 132),
			array('MTH', 'Marathi', 'OTPLS4', 133),
			array('MAR', 'Marshallese', 'OTPLS4', 134),
			array('MIE', 'Mien', 'OTPLS4', 135),
			array('MIR', 'Mirpuri', 'OTPLS4', 136),
			array('MIX', 'Mixteco Alto', 'OTPLS4', 137),
			array('MIB', 'Mixteco Bajo', 'OTPLS4', 138),
			array('MOL', 'Moldovan', 'OTPLS4', 139),
			array('MON', 'Mongolian', 'OTPLS4', 140),
			array('MNT', 'Montenegrin', 'OTPLS4', 141),
			array('MOR', 'Moroccan Arabic', 'OTPLS4', 142),
			array('NAH', 'Nahuat', 'OTPLS4', 143),
			array('NAV', 'Navajo', 'OTPLS4', 144),
			array('NEO', 'Neopolitan', 'OTPLS4', 145),
			array('NEP', 'Nepali', 'OTPLS4', 146),
			array('NIG', 'Nigerian English Pidgin', 'OTPLS4', 147),
			array('NOR', 'Norwegian', 'OTPLS4', 148),
			array('NUE', 'Nuer', 'OTPLS3', 149),
			array('NUP', 'Nupe', 'OTPLS4', 150),
			array('ORO', 'Oromo', 'OTPLS4', 151),
			array('OTE', 'Otetela', 'OTPLS4', 152),
			array('PAN', 'Pangasinan', 'OTPLS4', 153),
			array('PAS', 'Pashto', 'OTPLS4', 154),
			array('PDE', 'Pidgin English', 'OTPLS4', 155),
			array('POL', 'Polish', 'OTPLS4', 156),
			array('POR', 'Portuguese', 'OTPLS3', 157),
			array('PRC', 'Portuguese Creole', 'OTPLS4', 158),
			array('PUN', 'Punjabi', 'OTPLS4', 159),
			array('QUI', 'Quiche', 'OTPLS4', 160),
			array('ROM', 'Romanian', 'OTPLS4', 161),
			array('RUS', 'Russian', 'OTPLS3', 162),
			array('SAM', 'Samoan', 'OTPLS4', 163),
			array('SAR', 'Sarahuleh/Soninke', 'OTPLS4', 164),
			array('SER', 'Serbian', 'OTPLS4', 165),
			array('SEC', 'Sierbo-Croatian', 'OTPLS4', 166),
			array('SHA', 'Shanghainese', 'OTPLS4', 167),
			array('SCH', 'Sichuan', 'OTPLS4', 168),
			array('SIC', 'Sicilian', 'OTPLS4', 169),
			array('SND', 'Sindi', 'OTPLS4', 170),
			array('SIN', 'Sinhala', 'OTPLS4', 171),
			array('SLO', 'Slovak', 'OTPLS4', 172),
			array('SVN', 'Slovene', 'OTPLS3', 173),
			array('SOM', 'Somali', 'OTPLS2', 174),
			array('SOR', 'Sorani', 'OTPLS4', 175),
			array('SPA', 'Spanish', 'OTPLS1', 176),
			array('SUD', 'Sudanese Arabic', 'OTPLS4', 177),
			array('SUS', 'Susu', 'OTPLS4', 178),
			array('SWA', 'Swahili', 'OTPLS4', 179),
			array('SWE', 'Swedish', 'OTPLS4', 180),
			array('SYL', 'Sylheti', 'OTPLS4', 181),
			array('TAE', 'Taechew', 'OTPLS4', 182),
			array('TAG', 'Tagalog', 'OTPLS4', 183),
			array('TAI', 'Taiwanese', 'OTPLS4', 184),
			array('TAJ', 'Tajik', 'OTPLS4', 185),
			array('TAM', 'Tamil', 'OTPLS4', 186),
			array('TED', 'Tedim', 'OTPLS4', 187),
			array('TEL', 'Telegu', 'OTPLS4', 188),
			array('TET', 'Tetela', 'OTPLS4', 189),
			array('THA', 'Thai', 'OTPLS4', 190),
			array('TIB', 'Tibetan', 'OTPLS4', 191),
			array('TIG', 'Tigrigna', 'OTPLS4', 192),
			array('TOI', 'Toisan', 'OTPLS4', 193),
			array('TON', 'Tongan', 'OTPLS4', 194),
			array('TRU', 'Trukese', 'OTPLS4', 195),
			array('TUR', 'Turkish', 'OTPLS3', 196),
			array('TWI', 'Twi', 'OTPLS4', 197),
			array('URD', 'Urdu', 'OTPLS3', 199),
			array('UZB', 'Uzbek', 'OTPLS4', 200),
			array('VIE', 'Vietnamese', 'OTPLS2', 201),
			array('VIS', 'Visayan', 'OTPLS4', 202),
			array('WLL', 'Wollose', 'OTPLS4', 203),
			array('WOL', 'Wolof', 'OTPLS4', 204),
			array('YEM', 'Yemeni Arabic', 'OTPLS4', 205),
			array('YID', 'Yiddish', 'OTPLS4', 206),
			array('YOR', 'Yoruba', 'OTPLS4', 207),
			array('ZAN', 'Zande', 'OTPLS4', 208),
			array('ZOO', 'Zo', 'OTPLS4', 209),
			array('ZOM', 'Zomi', 'OTPLS4', 210),
			array('ZUL', 'Zulu', 'OTPLS4', 211),
			array('NEW', 'Newari', 'OTPLS4', 212),
			array('KEJ', 'Kejia', 'OTPLS4', 213),
			array('ZOP', 'Zophei', 'OTPLS4', 214),
			array('MIZ', 'Mizo', 'OTPLS4', 215),
			array('FAL', 'Falam', 'OTPLS4', 216),
			array('TMG', 'Tamang', 'OTPLS4', 217),
			array('QAN', 'Qanjobalan', 'OTPLS4', 218),
			array('TMP', 'Unlisted Language', 'OTPLS1', 219),
			array('CHJ', 'Chuj', 'OTPLS4', 220),
			array('UKR', 'Ukranian', 'OTPLS4', 221),
			array('AKT', 'Akateco', 'OTPLS4', 222),
        );

        public $indexIso639 = 0;
        public $indexLanguageName = 1;
        public $indexLanguageSet = 2;
        public $indexReferenceNumber = 3;

		/**
		 * Called when the object is created
		 */
		public function __construct() {
			if (is_array($this->languages)) {
				//sort the languages by language name
				$language = array_column($this->languages, 1);
				array_multisort($language, SORT_ASC, $this->languages);
			}
		}

        /*
        * Get all language data
        *
        * @return (array)
        */
        public function allLanguages()
        {
            return $this->languages;
        }

        /*
        * Get language name from ISO-639 (three-letter codes) terminologic
        *
        * @return (string)
        */
        public function languageByCode($code)
        {
            $code = strtolower(trim($code));

            foreach ($this->languages as $lang) {
                if ($lang[$this->indexIso639] === $code) {
                    return $lang[$this->indexLanguageName];
                }
            }

            return '';
        }

        /*
        * Get ISO-639 (three-letter codes) terminologic from language name
        *
        * @return (string)
        */
        public function codeByLanguage($language)
        {
            $language_key = strtolower(trim($language));

            foreach ($this->languages as $lang) {
                if (in_array($language_key, explode(', ', strtolower($lang[$this->indexLanguageName])))) {
                    return $lang[$this->indexIso639];
                }
            }

            return '';
        }

        /*
        * Get language set from language name
        *
        * @return (string)
        */
        public function languageSetByLanguage($language)
        {
            $language_key = strtolower(trim($language));

            foreach ($this->languages as $lang) {
                if (in_array($language_key, explode(', ', strtolower($lang[$this->indexLanguageName])))) {
                    return $lang[$this->indexLanguageSet];
                }
            }

            return '';
        }

        /**
         * Gat language array from ISO-639 (three-letter code)
         *
         * @param $code
         * @return array|null
         */
        public function getLanguageByIsoCode($code)
        {
            $code = strtolower(trim($code));

            foreach ($this->languages as $lang) {
                if ($lang[$this->indexIso639] === $code) {
                    return $lang;
                }
            }

            return null;
        }

        /**
         * Gat language array from language name
         *
         * @param $language
         * @return array|null
         */
        public function getLanguageByLanguageName($language)
        {
            $language = strtolower(trim($language));

            foreach ($this->languages as $lang) {
                if ($lang[$this->indexLanguageName] === $language) {
                    return $lang;
                }
            }

            return null;
        }
    }
}
