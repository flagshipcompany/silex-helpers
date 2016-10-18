<?php

namespace Flagship\Components\Helpers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class FormProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['flagship.helpers.forms.getErrorMessages'] = $app->protect(function (\Symfony\Component\Form\Form $form) use ($app) {
            $errors = [];
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
                if ($child->isValid()) {
                    continue;
                }
                $name = $child->getName();
                if (isset($errors[$name])) {
                    // If it's not an array, we make it an array so we can append errors to it
                    $errors[$name] = is_array($errors[$name]) ? $errors[$name] : [$errors[$name]];
                    $errors[$name][] = $app['flagship.helpers.forms.getErrorMessages']($child);

                    continue;
                }
                $errors[$name] = $app['flagship.helpers.forms.getErrorMessages']($child);
            }

            return $errors;
        });

        $app['flagship.helpers.forms.errorsArrayToString'] = $app->protect(function (array $errors) use ($app) {
            $string = '';
            array_walk($errors, function ($item, $key) use (&$string, $app) {
                if (is_array($item)) {
                    $string .= $key.'->'.$app['flagship.helpers.forms.errorsArrayToString']($item);

                    return;
                }
                $string .= $item.', ';
            });

            return $string;
        });
    }
}
