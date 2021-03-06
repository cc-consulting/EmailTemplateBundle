<?php

namespace CCC\EmailTemplateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use CCC\EmailTemplateBundle\Entity\EmailTemplate;
use CCC\EmailTemplateBundle\Form\Type\EmailTemplateType;
use CCC\EmailTemplateBundle\Form\Type\EmailTemplateSelectTestType;

/**
 * EmailTemplate controller.
 * @todo Pay attention to authentication; security in general.
 */
class EmailTemplateController extends Controller
{
    private $repository;

    /**
     * Lazily load the repository.  @todo Can we do this more like FOSUserBundle such that we don't have to instantiate
     * the EmailTemplate class?
     */
    private function getRepository()
    {
        if (! $this->repository) {
            $em = $this->getDoctrine()->getManager();
            $this->repository = $em->getRepository(get_class($this->get('ccc_email_template.entity')));
        }
        return $this->repository;
    }

    /**
     * Lists all EmailTemplate entities.
     *
     */
    public function indexAction()
    {
        $entities = $this->getRepository()->findAll();
        return $this->render('CCCEmailTemplateBundle:EmailTemplate:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new EmailTemplate entity.
     *
     */
    public function createAction(Request $request)
    {
        $form = $this->createCreateForm();
        $form->handleRequest($request);
        $entity = $form->getData();
        $entity->setUser($this->get('security.context')->getToken()->getUser());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            // Back to where we came from
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('CCCEmailTemplateBundle:EmailTemplate:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a EmailTemplate entity.
    *
    * @param EmailTemplate $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(EmailTemplate $entity = null)
    {
        $form = $this->createForm($this->get('ccc_email_template.form.type.create'), $entity, array(
            'action' => $this->generateUrl('ccc_email_template_create'),
            'method' => 'POST',
            'attr' => array('id' => 'ccc_email_template_new')
        ));

// @todo Let the rendering template add the submit button?
//        $form->add('submit', 'submit', array(
//            'label' => 'ccc.email-template.create',
//            'translation_domain' => 'CCCEmailTemplate',
//            'attr' => array('class' => 'btn')
//        ));

        return $form;
    }

    /**
     * Displays a form to create a new EmailTemplate entity.
     *
     */
    public function newAction()
    {
        $form = $this->createCreateForm();

        return $this->render('CCCEmailTemplateBundle:EmailTemplate:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a EmailTemplate entity.
     *
     */
    public function showAction($id)
    {
        $entity = $this->getRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EmailTemplate entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('CCCEmailTemplateBundle:EmailTemplate:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing EmailTemplate entity.
     *
     */
    public function editAction($id)
    {
        $entity = $this->getRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EmailTemplate entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('CCCEmailTemplateBundle:EmailTemplate:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a EmailTemplate entity.
    *
    * @param EmailTemplate $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(EmailTemplate $entity)
    {
        $form = $this->createForm(new EmailTemplateType(), $entity, array(
            'action' => $this->generateUrl('ccc_email_template_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array(
            'label' => 'ccc.email-template.update', 
            'translation_domain' => 'CCCEmailTemplate',
            'attr' => array('class' => 'btn')
        ));

        return $form;
    }
    /**
     * Edits an existing EmailTemplate entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->getRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EmailTemplate entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('ccc_email_template_show', array('id' => $entity->getId())));
        }

        return $this->render('CCCEmailTemplateBundle:EmailTemplate:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a EmailTemplate entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity = $this->getRepository()->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find EmailTemplate entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ccc_email_template'));
    }

    /**
     * Creates a form to delete a EmailTemplate entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ccc_email_template_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array(
                'label' => 'ccc.email-template.delete', 
                'translation_domain' => 'CCCEmailTemplate', 
                'attr' => array('class' => 'btn')
            ))
            ->getForm()
        ;
    }

    /**
     * Get email template message in JSON
     */
    public function jsonAction($id)
    {
        $entity = $this->getRepository()->find($id);

        $response = new Response(json_encode(strip_tags($entity->getTemplate())));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * Displays a list of templates you can select from and populate textarea
     *
     */
    public function selectAction()
    {
        $form   = $this->createSelectForm();

        return $this->render('CCCEmailTemplateBundle:EmailTemplate:select.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    

    /**
    * Creates a form to select EmailTemplate from list and populate template into textarea.
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createSelectForm()
    {
        $form = $this->createForm(new EmailTemplateSelectTestType($this->getDoctrine()->getManager()), array(
            'action' => $this->generateUrl('ccc_email_template_select'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'EmailTemplateSelectTest';
    }
}
