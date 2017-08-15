<?php

class Mail extends MailCore
{
    public static function Send(
        $idLang,
        $template,
        $subject,
        $templateVars,
        $to,
        $toName = null,
        $from = null,
        $fromName = null,
        $fileAttachment = null,
        $modeSmtp = null,
        $templatePath = _PS_MAIL_DIR_,
        $die = false,
        $idShop = null,
        $bcc = null,
        $replyTo = null,
        array $mandrillParams = [],
        $async = false,
        $ipPool = null,
        $sendAt = null
    ) {
        // Returns immediately if emails are deactivated
        if ((int) Configuration::get('PS_MAIL_METHOD') === 3) {
            return true;
        }

        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }

        if (!class_exists('Mandrill')) {
            require_once _PS_MODULE_DIR_.'mandrill/mandrill.php';
        }

        $themePath = _PS_THEME_DIR_;

        // Get the path of theme by id_shop if exist
        $shop = new Shop((int) $idShop);
        $themeName = $shop->getTheme();

        if (_THEME_NAME_ != $themeName) {
            $themePath = _PS_ROOT_DIR_.'/themes/'.$themeName.'/';
        }

        // Sending an e-mail can be of vital importance for the merchant, when his password is lost for example, so we must not die but do our best to send the e-mail
        if (!isset($from) || !Validate::isEmail($from)) {
            $from = Configuration::get('PS_SHOP_EMAIL');
        }

        if (!Validate::isEmail($from)) {
            $from = null;
        }

        // $from_name is not that important, no need to die if it is not valid
        if (!isset($fromName) || !Validate::isMailName($fromName)) {
            $fromName = Configuration::get('PS_SHOP_NAME');
        }
        if (!Validate::isMailName($fromName)) {
            $fromName = null;
        }

        // It would be difficult to send an e-mail if the e-mail is not valid, so this time we can die if there is a problem
        if (!is_array($to) && !Validate::isEmail($to)) {
            Tools::dieOrLog(Tools::displayError('Error: parameter "to" is corrupted'), $die);

            return false;
        }

        // if bcc is not null, make sure it's a vaild e-mail
        if (!is_null($bcc) && !is_array($bcc) && !Validate::isEmail($bcc)) {
            Tools::dieOrLog(Tools::displayError('Error: parameter "bcc" is corrupted'), $die);
            $bcc = null;
        }

        if (!is_array($templateVars)) {
            $templateVars = [];
        }

        // Do not crash for this error, that may be a complicated customer name
        if (is_string($toName) && !empty($toName) && !Validate::isMailName($toName)) {
            $toName = null;
        }

        if (!Validate::isTplName($template)) {
            Tools::dieOrLog(Tools::displayError('Error: invalid e-mail template'), $die);

            return false;
        }

        if (!Validate::isMailSubject($subject)) {
            Tools::dieOrLog(Tools::displayError('Error: invalid e-mail subject'), $die);

            return false;
        }

        /* Construct multiple recipients list if needed */
        $message = ['to' => [], 'headers' => [], 'images' => [], 'attachments' => []];
        if (is_array($to)) {
            foreach ($to as $key => $addr) {
                $addr = trim($addr);
                if (!Validate::isEmail($addr)) {
                    Tools::dieOrLog(Tools::displayError('Error: invalid e-mail address'), $die);

                    return false;
                }

                if (is_array($toName) && $toName && is_array($toName) && Validate::isGenericName($toName[$key])) {
                    $toName = $toName[$key];
                }

                $toName = (($toName == null || $toName == $addr) ? '' : static::mimeEncode($toName));
                $message['to'][] = [
                    'email' => $addr,
                    'to'    => $toName,
                    'type'  => 'to',
                ];
            }
        } else {
            /* Simple recipient, one address */
            $toName = (($toName == null || $toName == $to) ? '' : static::mimeEncode($toName));
            $message['to'][]  = [
                'email' => $to,
                'name'  => $toName,
                'type'  => 'to',
            ];
        }

        if ($bcc) {
            if (is_array($bcc)) {
                if (is_string($bcc[0])) {
                    $bcc = [$bcc];
                }
            } else {
                $bcc = [
                    ['email' => $bcc],
                ];
            }

            $bccs = [];
            foreach ($bcc as $receiver) {
                $mandrillBcc = [
                    'email' => $receiver['email'],
                    'type'  => 'bcc',
                ];
                if (isset($receiver['name']) && $receiver['name']) {
                    $mandrillBcc['name'] = $receiver['name'];
                }

                $bccs[] = $mandrillBcc;
            }
            $message['to'] += $bccs;
        }

        try {
            $mandrill = new MandrillModule\Mandrill(Configuration::get(Mandrill::API_KEY));
            /* Get templates content */
            $iso = Language::getIsoById((int) $idLang);
            if (!$iso) {
                Tools::dieOrLog(Tools::displayError('Error - No ISO code for email'), $die);

                return false;
            }
            $isoTemplate = $iso.'/'.$template;

            $moduleName = false;
            $overrideMail = false;

            // get templatePath
            if (preg_match('#'.$shop->physical_uri.'modules/#', str_replace(DIRECTORY_SEPARATOR, '/', $templatePath)) && preg_match('#modules/([a-z0-9_-]+)/#ui', str_replace(DIRECTORY_SEPARATOR, '/', $templatePath), $res)) {
                $moduleName = $res[1];
            }

            if ($moduleName !== false && (file_exists($themePath.'modules/'.$moduleName.'/mails/'.$isoTemplate.'.txt') ||
                    file_exists($themePath.'modules/'.$moduleName.'/mails/'.$isoTemplate.'.html'))
            ) {
                $templatePath = $themePath.'modules/'.$moduleName.'/mails/';
            } elseif (file_exists($themePath.'mails/'.$isoTemplate.'.txt') || file_exists($themePath.'mails/'.$isoTemplate.'.html')) {
                $templatePath = $themePath.'mails/';
                $overrideMail = true;
            }
            if (!file_exists($templatePath.$isoTemplate.'.txt') && ((int) Configuration::get('PS_MAIL_TYPE') === Mail::TYPE_BOTH || (int) Configuration::get('PS_MAIL_TYPE') === Mail::TYPE_TEXT)) {
                Tools::dieOrLog(Tools::displayError('Error - The following e-mail template is missing:').' '.$templatePath.$isoTemplate.'.txt', $die);

                return false;
            } elseif (!file_exists($templatePath.$isoTemplate.'.html') && ((int) Configuration::get('PS_MAIL_TYPE') === Mail::TYPE_BOTH || Configuration::get('PS_MAIL_TYPE') === Mail::TYPE_HTML)) {
                Tools::dieOrLog(Tools::displayError('Error - The following e-mail template is missing:').' '.$templatePath.$isoTemplate.'.html', $die);

                return false;
            }
            $templateHtml = '';
            $templateTxt = '';
            Hook::exec(
                'actionEmailAddBeforeContent',
                [
                    'template'      => $template,
                    'template_html' => &$templateHtml,
                    'template_txt'  => &$templateTxt,
                    'id_lang'       => (int) $idLang,
                ],
                null,
                true
            );
            $templateHtml .= file_get_contents($templatePath.$isoTemplate.'.html');
            $templateTxt .= strip_tags(html_entity_decode(file_get_contents($templatePath.$isoTemplate.'.txt'), null, 'utf-8'));
            Hook::exec(
                'actionEmailAddAfterContent',
                [
                    'template'      => $template,
                    'template_html' => &$templateHtml,
                    'template_txt'  => &$templateTxt,
                    'id_lang'       => (int) $idLang,
                ],
                null,
                true
            );
            if ($overrideMail && file_exists($templatePath.$iso.'/lang.php')) {
                include_once($templatePath.$iso.'/lang.php');
            } elseif ($moduleName && file_exists($themePath.'mails/'.$iso.'/lang.php')) {
                include_once($themePath.'mails/'.$iso.'/lang.php');
            } elseif (file_exists(_PS_MAIL_DIR_.$iso.'/lang.php')) {
                include_once(_PS_MAIL_DIR_.$iso.'/lang.php');
            } else {
                Tools::dieOrLog(Tools::displayError('Error - The language file is missing for:').' '.$iso, $die);

                return false;
            }

            /* Create mail and attach differents parts */
            $subject = '['.Configuration::get('PS_SHOP_NAME', null, null, $idShop).'] '.$subject;
            $message['subject'] = $subject;

            if (!$replyTo) {
                $replyTo = $from;
            }

            if (isset($replyTo) && $replyTo) {
                $message['headers'][] = 'Reply-To: '.$replyTo;
            }

            if (Configuration::get('PS_LOGO_MAIL') !== false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $idShop))) {
                $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $idShop);
            } else {
                if (file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $idShop))) {
                    $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $idShop);
                } else {
                    $templateVars['{shop_logo}'] = '';
                }
            }
            ShopUrl::cacheMainDomainForShop((int) $idShop);
            /* Don't attach the logo as attachment */
            if (isset($logo)) {
                $mime = substr($logo, -4) === '.png' ? 'png' : 'jpeg';
                $message['images'][] = [
                    'type'    => 'image/'.$mime,
                    'name'    => 'shoplogo',
                    'content' => base64_encode(file_get_contents($logo)),
                ];
                $templateVars['{shop_logo}'] = 'cid:shoplogo';
            }

            if ((Context::getContext()->link instanceof Link) === false) {
                Context::getContext()->link = new Link();
            }

            $templateVars['{shop_name}'] = Tools::safeOutput(Configuration::get('PS_SHOP_NAME', null, null, $idShop));
            $templateVars['{shop_url}'] = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, null, false, $idShop);
            $templateVars['{my_account_url}'] = Context::getContext()->link->getPageLink('my-account', true, Context::getContext()->language->id, null, false, $idShop);
            $templateVars['{guest_tracking_url}'] = Context::getContext()->link->getPageLink('guest-tracking', true, Context::getContext()->language->id, null, false, $idShop);
            $templateVars['{history_url}'] = Context::getContext()->link->getPageLink('history', true, Context::getContext()->language->id, null, false, $idShop);
            $templateVars['{color}'] = Tools::safeOutput(Configuration::get('PS_MAIL_COLOR', null, null, $idShop));
            // Get extra template_vars
            $extraTemplateVars = [];
            Hook::exec(
                'actionGetExtraMailTemplateVars',
                [
                    'template'            => $template,
                    'template_vars'       => $templateVars,
                    'extra_template_vars' => &$extraTemplateVars,
                    'id_lang'             => (int) $idLang,
                ],
                null,
                true
            );
            $templateVars = array_merge($templateVars, $extraTemplateVars);
            foreach ($templateVars as $search => $replace) {
                $templateTxt = str_replace($search, $replace, $templateTxt);
                $templateHtml = str_replace($search, $replace, $templateHtml);
            }
            if ($fileAttachment && !empty($fileAttachment)) {
                // Multiple attachments?
                if (!is_array(current($fileAttachment))) {
                    $fileAttachment = [$fileAttachment];
                }

                foreach ($fileAttachment as $attachment) {
                    if (isset($attachment['content']) && isset($attachment['name']) && isset($attachment['mime'])) {
                        $message['attachments'][] = [
                            'type'    => $attachment['mime'],
                            'name'    => $attachment['name'],
                            'content' => base64_encode($attachment['content']),
                        ];
                    }
                }
            }
            /* Send mail */
            $message['from_email'] = $from;
            $message['from_name'] = $fromName;
            if ((int) Configuration::get('PS_MAIL_METHOD') === Mail::TYPE_BOTH || (int) Configuration::get('PS_MAIL_METHOD') === Mail::TYPE_HTML) {
                $message['html'] = $templateHtml;
            }
            if ((int) Configuration::get('PS_MAIL_METHOD') === Mail::TYPE_BOTH || (int) Configuration::get('PS_MAIL_METHOD') === Mail::TYPE_TEXT) {
                $message['text'] = $templateTxt;
            }

            $send = $mandrill->messages->send($message, $async, $ipPool, $sendAt);

            ShopUrl::resetMainDomainCache();

            if ($send && Configuration::get('PS_LOG_EMAILS')) {
                $mail = new Mail();
                $mail->template = Tools::substr($template, 0, 62);
                $mail->subject = Tools::substr($subject, 0, 254);
                $mail->id_lang = (int) $idLang;
                foreach (array_column($message['to'], 'email') as $email) {
                    $mail->id = null;
                    $mail->recipient = Tools::substr($email, 0, 126);
                    $mail->add();
                }
            }

            return count($message['to']);
        } catch (Swift_SwiftException $e) {
            Logger::addLog(
                'Mandrill Error: '.$e->getMessage(),
                3,
                null,
                'Mandrill'
            );

            return false;
        }
    }

    public static function sendMailTest($smtpChecked, $smtpServer, $content, $subject, $type, $to, $from, $smtpLogin, $smtpPassword, $smtpPort = 25, $smtpEncryption)
    {
        if (!class_exists('Mandrill')) {
            require_once _PS_MODULE_DIR_.'mandrill/mandrill.php';
        }

        try {
            $mandrill = new \MandrillModule\Mandrill(Configuration::get(Mandrill::API_KEY));
            $message = [
                'html'       => $content,
                'from_email' => $from,
                'to'         => [
                    ['email' => $to],
                ],
                'subject' => $subject,
            ];

            $mandrill->messages->send($message, true);

            $result = true;
        } catch (Swift_SwiftException $e) {
            $result = $e->getMessage();
        }

        return $result;
    }
}
