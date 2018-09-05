<?php

namespace AppBundle\Controller\Api;

use AppBundle\Api\ApiProblem;
use AppBundle\Api\ApiProblemException;
use AppBundle\Controller\BaseController;
use AppBundle\Entity\Programmer;
use AppBundle\Form\ProgrammerType;
use AppBundle\Form\UpdateProgrammerType;
use AppBundle\Pagination\PaginatedCollection;
use AppBundle\Pagination\PaginationFactory;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

///**
// * @Security("is_granted('ROLE_USER')")
// */
class ProgrammerController extends BaseController
{
    /**
     * @Route("/api/programmers", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $programmer = new Programmer();
        $form = $this->createForm(ProgrammerType::class, $programmer);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $programmer->setUser($this->findUserByUsername('weaverryan'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($programmer);
        $em->flush();

        $response = $this->createApiResponse($programmer, 201);
        $response->headers
            ->set(
                'Location',
                $this->generateUrl('api_programmers_show', [
                    'nickname' => $programmer->getNickname()
                ])
            );

        return $response;

    }

    /**
     * @Route("/api/programmers/{nickname}", name="api_programmers_show", methods={"GET"})
     * @param Programmer $programmer
     * @return Response
     */
    public function showAction(Programmer $programmer)
    {
        return  $this->createApiResponse($programmer, 200);
    }

    /**
     * @param Request $request
     * @param PaginationFactory $paginationFactory
     * @Route("/api/programmers", name="api_programmers_collection", methods={"GET"})
     * @return Response
     */
    public function listAction(Request $request, PaginationFactory $paginationFactory)
    {
        $filter = $request->query->get('filter');
        $qb = $this->getDoctrine()
            ->getRepository(Programmer::class)
            ->findAllQueryBuilder($filter);

        $paginatedCollection = $paginationFactory
            ->createCollection($qb, $request, 'api_programmers_collection');

        return $this->createApiResponse($paginatedCollection);
    }

    /**
     * @param Programmer $programmer
     * @param Request $request
     * @Route("/api/programmers/{nickname}", methods={"PUT", "PATCH"})
     * @return Response
     */
    public function updateAction(Programmer $programmer, Request $request)
    {

        $form = $this->createForm(UpdateProgrammerType::class, $programmer);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($programmer);
        $em->flush();

        $response = $this->createApiResponse($programmer, 200);

        return $response;
    }

    /**
     * @Route("/api/programmers/{nickname}", methods={"DELETE"})
     * @param Programmer $programmer
     * @return Response
     */
    public function deleteAction(Programmer $programmer)
    {
        if ($programmer) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($programmer);
            $em->flush();
        }

        return new Response(null, 204);
    }

    private function processForm(Request $request, FormInterface $form)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);

            throw new ApiProblemException($apiProblem);
        }

        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }

    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    /**
     * @param FormInterface $form
     */
    private function throwApiProblemValidationException(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);

        $apiProblem = new ApiProblem(
            400,
            ApiProblem::TYPE_VALIDATION_ERROR
        );
        $apiProblem->set('errors', $errors);

        throw new ApiProblemException($apiProblem);
    }
}
