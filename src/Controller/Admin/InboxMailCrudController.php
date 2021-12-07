<?php

namespace App\Controller\Admin;

use App\Entity\InboxMail;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Html2Text\Html2Text;

class InboxMailCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return InboxMail::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $support = AssociationField::new('support')->setLabel('Запрос')
        ;
        $body = null;

        if (in_array($pageName, [Crud::PAGE_DETAIL, Crud::PAGE_EDIT])) {
            yield $support;
            yield EmailField::new('fromEmail')->setLabel('От (email)')
                ->setDisabled();
            yield TextField::new('fromName')->setLabel('От');

            yield TextField::new('subj')->setLabel('Тема')
                ->setDisabled();
        }

        if (Crud::PAGE_DETAIL == $pageName) {
            $body = TextareaField::new('body')->setLabel('Текст письма')
                ->formatValue(function ($text) {
                    return html_entity_decode($text);
                })
                ;
        } elseif (Crud::PAGE_EDIT == $pageName) {
            $body = TextEditorField::new('body')->setLabel('Текст письма (НЕ ИЗМЕНЯТЬ!)')
                ->setDisabled();
            ;
        } elseif (Crud::PAGE_INDEX === $pageName) {
            yield $support;
            yield TextField::new('fromFull')->setLabel('От');
            yield TextField::new('subj')->setLabel('Тема')
                ->setColumns(3)
                ->setMaxLength(50);
            $body = TextareaField::new('body')->setLabel('Текст письма')
                ->formatValue(function ($text) {
                    $html = new Html2Text($text);
                    return $html->getText();
                })
                ->setMaxLength(1000)
                ->setColumns(1);
        }
        yield ChoiceField::new('state')->setLabel('Статус')
            ->setChoices([
                'Новый' => 'new_to_check',
                'Наш BCC' => 'our_bcc',
                '??? Повторное обращение' => 'potential_req_id',
                'Новое обращение' => 'has_no_req_id',
                'Существующее обращение' => 'has_valid_req_id',
                'Неправильный ID' => 'has_invalid_req_id',
                'Отправлен автоответ' => 'sent_autoreplay_to_new',
                '??? Спам' => 'potential_spam',
                '++Отправить автоответ' => 'prepare_for_send',
            ]);
        yield DateTimeField::new('received')->setLabel('Когда пришло')
            ->setDisabled();
        yield $body;

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Входящее')
            ->setEntityLabelInPlural('Входящие')
            ->setPaginatorPageSize(30)
            ->setDefaultSort(['received' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }
}
