<?php

declare(strict_types=1);

/*
 * This file is part of the TranslationFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\TranslationFormBundle\Tests\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use A2lix\TranslationFormBundle\Tests\Fixtures\Entity\Product;
use A2lix\TranslationFormBundle\Tests\Form\TypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\PreloadedExtension;

/**
 * @internal
 */
final class TranslationsTypeAdvancedTest extends TypeTestCase
{
    protected $locales = ['en', 'fr', 'de'];
    protected $defaultLocale = 'en';
    protected $requiredLocales = ['en', 'fr'];

    public function testEmptyFormOverrideLocales(): void
    {
        $overrideLocales = ['en', 'fr', 'es'];
        $overrideRequiredLocales = ['en', 'es'];

        $form = $this->factory->createBuilder(FormType::class, new Product())
            ->add('url')
            ->add('translations', TranslationsType::class, [
                'locales' => $overrideLocales,
                'required_locales' => $overrideRequiredLocales,
            ])
            ->add('save', SubmitType::class)
            ->getForm()
        ;

        $translationsForm = $form->get('translations')->all();
        $translationsLocales = array_keys($translationsForm);
        $translationsRequiredLocales = array_keys(array_filter($translationsForm, function ($form) {
            return $form->isRequired();
        }));

        static::assertEquals($overrideLocales, $translationsLocales, 'Locales should be same as config');
        static::assertEquals($overrideRequiredLocales, $translationsRequiredLocales, 'Required locales should be same as config');

        static::assertEquals(['title', 'description'], array_keys($translationsForm['en']->all()), 'Fields should matches ProductTranslation fields');
        static::assertEquals(['title', 'description'], array_keys($translationsForm['fr']->all()), 'Fields should matches ProductTranslation fields');
        static::assertEquals(['title', 'description'], array_keys($translationsForm['es']->all()), 'Fields should matches ProductTranslation fields');
    }

    public function testEmptyFormOverrideFields(): void
    {
        $form = $this->factory->createBuilder(FormType::class, new Product())
            ->add('url')
            ->add('translations', TranslationsType::class, [
                'excluded_fields' => ['description'],
            ])
            ->add('save', SubmitType::class)
            ->getForm()
        ;

        $translationsForm = $form->get('translations')->all();
        static::assertEquals(['title'], array_keys($translationsForm['en']->all()), 'Fields should not contains description');
        static::assertEquals(['title'], array_keys($translationsForm['fr']->all()), 'Fields should not contains description');
        static::assertEquals(['title'], array_keys($translationsForm['de']->all()), 'Fields should not contains description');
    }

    public function testLabels(): void
    {
        $form = $this->factory->createBuilder(FormType::class, new Product())
            ->add('url')
            ->add('translations', TranslationsType::class, [
                'locale_labels' => [
                    'fr' => 'Français',
                    'en' => 'English',
                ],
            ])
            ->add('save', SubmitType::class)
            ->getForm()
        ;

        $translationsForm = $form->get('translations')->all();
        static::assertEquals('English', $translationsForm['en']->getConfig()->getOptions()['label'], 'Label should be explicitely set');
        static::assertEquals('Français', $translationsForm['fr']->getConfig()->getOptions()['label'], 'Label should be explicitely set');
        static::assertNull($translationsForm['de']->getConfig()->getOptions()['label'], 'Label should default to null');
    }

    protected function getExtensions(): array
    {
        $translationsType = $this->getConfiguredTranslationsType($this->locales, $this->defaultLocale, $this->requiredLocales);
        $autoFormType = $this->getConfiguredAutoFormType();

        return [new PreloadedExtension([
            $translationsType,
            $autoFormType,
        ], [])];
    }
}
