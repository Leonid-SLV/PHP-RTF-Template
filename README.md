PHP RTF Template
=======
Simple library for working with templates. Works on any PHP version, no additional libraries or dependencies are used. RTF files can be used from under any text editor, including MS Word.
Supported formats UTF-8 and CP1251.

The library looks for the text in the [template] view and replaces it with a value.

Examples:
---------------
More examples can be found in the /Examples

Simple easy example:

	$rtf = '{\rtf1\ansi\ansicpg1251\deff0\nouicompat\deflang1049{\fonttbl{\f0\fnil\fcharset0 Calibri;}{\f1\fnil\fcharset204 Calibri;}}
	{\*\generator Riched20 10.0.19041}\viewkind4\uc1
	\pard\sa200\sl276\slmult1\f0\fs22\lang1033 Hello\f1\lang1049  \f0\lang1033 [template], my name PHP RTF Template!\lang9\par
	}
	';
	$rtf = rtf_easy_replace($rtf,'template','world');

Simple easy example:

	$rtf = '{\rtf1\ansi\ansicpg1251\deff0\nouicompat\deflang1049{\fonttbl{\f0\fnil\fcharset0 Calibri;}{\f1\fnil\fcharset204 Calibri;}}
	{\*\generator Riched20 10.0.19041}\viewkind4\uc1
	\pard\sa200\sl276\slmult1\f0\fs22\lang1033 Hello\f1\lang1049  \f0\lang1033 [template], my name PHP RTF Template!\lang9\par
	}
	';
	$template = rtf2template($data);
    foreach ($template as $value)
      {
        if (rtf_compare('template','world')
          {
            $template = str_replace('['.'template'.']',text2rtf('world'),$rtf);
         }
      }

About:
=========================

	 *@Author	Selvistrovich Leonid
	 *@Mail		crack-it@yandex.com
	  @version	1.0, 02.12.2020

