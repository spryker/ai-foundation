<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\AiFoundation\Communication\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AiInteractionLogTableFilterForm extends AbstractType
{
    protected const string FIELD_CONFIGURATION_NAME = 'configuration_name';

    protected const string FIELD_IS_SUCCESSFUL = 'is_successful';

    protected const string FIELD_CONVERSATION_REFERENCE = 'conversation_reference';

    public const string FIELD_CREATED_AT_FROM = 'created_at_from';

    protected const string FIELD_CREATED_AT_TO = 'created_at_to';

    public const string OPTION_CONFIGURATION_NAME_CHOICES = 'configuration_name_choices';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addConfigurationNameField($builder, $options);
        $this->addIsSuccessfulField($builder);
        $this->addConversationReferenceField($builder);
        $this->addCreatedAtFromField($builder);
        $this->addCreatedAtToField($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
            static::OPTION_CONFIGURATION_NAME_CHOICES => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addConfigurationNameField(FormBuilderInterface $builder, array $options): self
    {
        $builder->add(static::FIELD_CONFIGURATION_NAME, ChoiceType::class, [
            'label' => 'Configuration',
            'choices' => $options[static::OPTION_CONFIGURATION_NAME_CHOICES],
            'required' => false,
            'placeholder' => 'All Configurations',
            'attr' => ['class' => 'spryker-form-select2combobox'],
        ]);

        return $this;
    }

    protected function addIsSuccessfulField(FormBuilderInterface $builder): self
    {
        $builder->add(static::FIELD_IS_SUCCESSFUL, ChoiceType::class, [
            'label' => 'Status',
            'choices' => [
                'Success' => '1',
                'Failed' => '0',
            ],
            'required' => false,
            'placeholder' => 'All Statuses',
            'attr' => ['class' => 'spryker-form-select2combobox'],
        ]);

        return $this;
    }

    protected function addConversationReferenceField(FormBuilderInterface $builder): self
    {
        $builder->add(static::FIELD_CONVERSATION_REFERENCE, TextType::class, [
            'label' => 'Conversation',
            'required' => false,
            'attr' => ['placeholder' => 'Exact match...'],
        ]);

        return $this;
    }

    protected function addCreatedAtFromField(FormBuilderInterface $builder): self
    {
        $builder->add(static::FIELD_CREATED_AT_FROM, DateType::class, [
            'label' => 'From Date',
            'widget' => 'single_text',
            'required' => false,
        ]);

        return $this;
    }

    protected function addCreatedAtToField(FormBuilderInterface $builder): self
    {
        $builder->add(static::FIELD_CREATED_AT_TO, DateType::class, [
            'label' => 'To Date',
            'widget' => 'single_text',
            'required' => false,
        ]);

        return $this;
    }
}
