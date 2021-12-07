<?php

namespace App\Controller\Admin;

use App\Entity\SupportRequest;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Html2Text\Html2Text;

class  SupportRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SupportRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Запрос')
            ->setEntityLabelInPlural('Запросы')
            ->setPaginatorPageSize(30)
            ->setDefaultSort(['received' => 'DESC']);
    }


    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reqId', '#')
            ->setDisabled()
        ;
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL])) {
            yield TextField::new('fromFull', 'От')
            ;
        }
        yield TextField::new('subject', 'Тема')
            ->setDisabled()
        ;
        yield DateTimeField::new('received', 'Получено')
            ->setDisabled()
        ;
        if (Crud::PAGE_INDEX == $pageName) {
            yield TextareaField::new('requestText')->setLabel('Запрос')
                ->formatValue(function ($text) {
                    $html = new Html2Text($text);
                    return $html->getText();
                })
                ->setMaxLength(100)
                ->setDisabled()
            ;
        } elseif (Crud::PAGE_EDIT == $pageName) {
            yield EmailField::new('fromEmail', 'От (email)')
                ->setDisabled()
            ;
            yield TextField::new('fromName', 'От');
            yield TextEditorField::new('requestText', 'Запрос (НЕ ИЗМЕНЯТЬ!)')
                ->setDisabled()
            ;
            yield TextEditorField::new('repliedText', 'Ответ (НЕ ИЗМЕНЯТЬ!)')
                ->setDisabled()
            ;
        } elseif (Crud::PAGE_DETAIL == $pageName) {
            yield TextareaField::new('requestText')->setLabel('Запрос')
                ->formatValue(function ($text) {
                    return html_entity_decode($text);
                })
            ;
            yield TextareaField::new('repliedText')->setLabel('Ответ')
                ->formatValue(function ($text) {
                    return html_entity_decode($text);
                })
            ;
        }

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
