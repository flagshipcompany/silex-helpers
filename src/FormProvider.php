<?php

namespace Flagship\Components\Helpers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class FormProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['flagship.helpers.forms.getErrorMessages'] = $app->protect(function (\Symfony\Component\Form\Form $form) use ($app) {
            $errors = array();
            $data = $form->getData();

            if (!$form->isSubmitted()) {
                $errors['#'] = 'No request data was supplied, or there was no valid data';

                return $errors;
            }

            foreach ($form->getErrors() as $key => $error) {
                if (empty($error->getMessage())) {
                    continue;
                }
                if ($form->isRoot()) {
                    $errors['#'][] = str_replace('"', '', $error->getMessage());
                } elseif (strpos($form->getName(), 'password') !== false) {
                    $message = $error->getMessage();
                    $errors[] = str_replace('"', '', $message);
                } else {
                    $message = empty($data) || is_array($data) ? $error->getMessage() : $data.' '.$error->getMessage();
                    $errors[] = str_replace('"', '', $message);
                }
            }

            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $app['flagship.helpers.forms.getErrorMessages']($child);
                }
            }

            return $errors;
        });
    }
}
