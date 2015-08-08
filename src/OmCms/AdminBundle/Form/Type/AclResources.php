<?php
namespace OmCms\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use OmCms\AdminBundle\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;

class AclResources extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    public function __construct(
        EntityManager $entityManager,
        TranslatorInterface $translator,
        TokenStorage $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'acl_resources';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['class' => 'Robo\AdministratorBundle\Entity\Resource', 'multiple' => true, 'selectedCell' => []]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $repository = $this->entityManager->getRepository('OmCmsAdminBundle:Resource');

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $visibleModules = null;

        /** @var Resource[] $rows */
        $rows = $repository->findBy([], ['sort' => 'ASC']);
        $choices = [];
        foreach ($rows as $row) {
            if (!is_null($visibleModules) && !in_array($row->getResource(), $visibleModules)) {
                continue;
            }
            $a = explode('\\', $row->getResource());
            $domain = $a[0] . $a[1];
            $label = $this->translator->trans($row->getLabel(), [], $domain);
            $group = $this->translator->trans($row->getGroupLabel(), [], $row->getGroupLabelDomain());
            $choices[$group][$label][$row->getAction()] = $row;
        }

        $actions = $repository->getActions();
        $view->vars['acl_data'] = $choices;
        $view->vars['acl_actions'] = $actions;
        $view->vars['selectedCell'] = $options['selectedCell'];
    }

    public function getParent()
    {
        return 'entity';
    }
}
