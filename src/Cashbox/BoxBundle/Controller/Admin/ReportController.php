<?php

namespace Cashbox\BoxBundle\Controller\Admin;

use APY\DataGridBundle\Grid\{Column, Export, Source\Vector};
use Cashbox\BoxBundle\Document\TillReport;
use Cashbox\BoxBundle\Form\ReportByPeriodForm;
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Repository\Form\ReportByPeriodFormType;
use Sonata\AdminBundle\Controller\CoreController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{Request, Session\SessionInterface};
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/report", name="report")
 *
 */
class ReportController extends CoreController
{
    const SESSION_PARAM_BY_FORM = 'form_sheet_';

    /**
     * @Route("/period", name="_period")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function periodAction(Request $request)
    {
        $session = $request->getSession();
        $session->start();

        $choiceOrganization = OrganizationModel::setChoiceOrganization($this->get('doctrine_mongodb'));

        $reportForm = new ReportByPeriodForm();
        $formReport = $this->createForm(ReportByPeriodFormType::class, $reportForm, [
            'organization' => $choiceOrganization,
            'attr' => [
                'class' => 'form-inline form-report'
            ]
        ]);
        $formReport->handleRequest($request);

        $dataForm = $formReport->getData();
        $this->setFormData($formReport, $dataForm, $session);

        $columns = [
            new Column\DateTimeColumn([
                'id' => 'date', 'field' => 'date', 'source' => true, 'title' => 'Date', 'format' => 'd.m.Y H:i:s'
            ]),
            new Column\TextColumn([
                'id' => 'typePayment', 'field' => 'typePayment', 'source' => true, 'title' => 'Payment'
            ]),
            new Column\TextColumn([
                'id' => 'tin', 'field' => 'tin', 'source' => true, 'title' => 'Organization'
            ]),
            new Column\TextColumn([
                'id' => 'type', 'field' => 'type', 'source' => true, 'title' => 'Type'
            ]),
            new Column\NumberColumn([
                'id' => 'orderSum', 'field' => 'orderSum', 'source' => true, 'title' => 'Sum', 'style' => 'currency'
            ]),
        ];

        $books = [];
        /**
         * @var TillReport[] $records
         */
        $records = $this->get('doctrine_mongodb')->getRepository(TillReport::class)
            ->findByPeriod($dataForm->getDateStart(), $dataForm->getDateEnd(), $dataForm->getTin());
        foreach ($records as $record) {
            $dataPost = $record->getDataPost();
            dump($dataPost);
            $orderSum = $dataPost['kkm']['payment']['cash'] ?? 0 + $dataPost['kkm']['payment']['card'] ?? 0;

            $tinRecord = $record->getTin();
            $books[] = [
                'date' => $record->getDatetime(),
                'typePayment' => $record->getTypePayment(),
                'tin' => (isset($choiceOrganization[$tinRecord]) ? $choiceOrganization[$tinRecord] : $tinRecord),
                'type' => $record->getType(),
                'orderSum' => $orderSum,
            ];
        }

        $source = new Vector($books, $columns);
        $source->setId(['date']);

        $grid = $this->get('grid');
        $grid->setSource($source);

        $grid->addExport(new Export\ExcelExport('Excel'));
        $grid->addExport(new Export\CSVExport('CSV'));
        $grid->addExport(new Export\JSONExport('JSON'));

        $formReportView = $formReport->createView();
        return $grid->getGridResponse('@Box/Admin/Report/grid_layout.html.twig', [
            'base_template' => $this->getBaseTemplate(),
            'admin_pool'    => $this->get('sonata.admin.pool'),
            'blocks'        => $this->getParameter('sonata.admin.configuration.dashboard_blocks'),
            'title'         => 'labels.groups.reports.sheet',
            'formReport'    => $formReportView,
        ]);
    }

    /**
     * Set default Data of Form
     *
     * @param FormInterface $form
     * @param ReportByPeriodForm $dataForm
     * @param SessionInterface $session
     */
    private function setFormData(FormInterface $form, ReportByPeriodForm &$dataForm, SessionInterface $session)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $datePeriodStart = $dataForm->getDateStart();
            $datePeriodEnd = $dataForm->getDateEnd();
            $tin = $dataForm->getTin();

            $session->set(self::SESSION_PARAM_BY_FORM . $form->getName(), [
                'dateStart' => $datePeriodStart,
                'dateEnd' => $datePeriodEnd,
                'organization' => $tin
            ]);
        } elseif (!$form->isSubmitted()) {
            $dataSheetSession = $session->get(self::SESSION_PARAM_BY_FORM . $form->getName());
            if (isset($dataSheetSession) && !is_null($dataSheetSession)) {
                $datePeriodStart = $dataSheetSession['dateStart'];
                $datePeriodEnd = $dataSheetSession['dateEnd'];
                $tin = $dataSheetSession['organization'];
            } else {
                $datePeriodStart = new \DateTime('first day of this month');
                $datePeriodEnd = new \DateTime('last day of this month');
                $tin = ReportByPeriodFormType::ALL_ORGANIZATION;
            }

            $dataForm->setDateStart($datePeriodStart);
            $dataForm->setDateEnd($datePeriodEnd);
            $dataForm->setTin($tin);
            $form->setData($dataForm);
        }
    }
}