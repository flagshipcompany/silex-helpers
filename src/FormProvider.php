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

            foreach ($form->getErrors() as $key => $error) {
                if ($form->isRoot()) {
                    $errors['#'][] = $error->getMessage();
                } else {
                    $errors[] = $error->getMessage();
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
