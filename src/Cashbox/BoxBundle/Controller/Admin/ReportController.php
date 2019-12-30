<?php

namespace Cashbox\BoxBundle\Controller\Admin;

use APY\DataGridBundle\Grid\{Column, Export, Source\Vector};
use Cashbox\BoxBundle\Document\ReportKKM;
use Cashbox\BoxBundle\Form\ReportByPeriodForm;
use Cashbox\BoxBundle\Repository\Form\ReportByPeriodFormType;
use Sonata\AdminBundle\Controller\CoreController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/report", name="report")
 *
 */
class ReportController extends CoreController
{
    /**
     * @Route("/period", name="_period")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function periodAction(Request $request)
    {
        $session = $request->getSession();
        $session->start();

        $reportForm = new ReportByPeriodForm();

        $formReport = $this->createForm(ReportByPeriodFormType::class, $reportForm, [
            'attr' => [
                'class' => 'form-inline form-report'
            ]
        ]);
        $formReport->handleRequest($request);

        $dataForm = $formReport->getData();

        $datePeriodStart = null;
        $datePeriodEnd = null;
        if ($formReport->isSubmitted() && $formReport->isValid()) {
            $datePeriodStart = $dataForm->getDateStart();
            $datePeriodEnd = $dataForm->getDateEnd();

            $session->set('form_sheet_'.$formReport->getName(), [
                'dateStart' => $datePeriodStart,
                'dateEnd' => $datePeriodEnd,
            ]);
        } elseif (!$formReport->isSubmitted()) {
            $dataSheetSession = $session->get('form_sheet_' . $formReport->getName());
            if (isset($dataSheetSession) && !is_null($dataSheetSession)) {
                $datePeriodStart = $dataSheetSession['dateStart'];
                $datePeriodEnd = $dataSheetSession['dateEnd'];
            } else {
                $datePeriodStart = new \DateTime('first day of this month');
                $datePeriodEnd = new \DateTime('last day of this month');
            }

            $dataForm->setDateStart($datePeriodStart);
            $dataForm->setDateEnd($datePeriodEnd);
            $formReport->setData($dataForm);
        }

        $columns = [
            new Column\DateTimeColumn(['id' => 'date', 'field' => 'date', 'source' => true, 'title' => 'Дата', 'format' => 'd.m.Y H:i:s']),
            new Column\TextColumn(['id' => 'typePayment', 'field' => 'typePayment', 'source' => true, 'title' => 'Платежная система']),
            new Column\TextColumn(['id' => 'INN', 'field' => 'INN', 'source' => true, 'title' => 'ИНН']),
            new Column\TextColumn(['id' => 'type', 'field' => 'type', 'source' => true, 'title' => 'Тип']),
            new Column\NumberColumn(['id' => 'orderSum', 'field' => 'orderSum', 'source' => true, 'title' => 'Сумма', 'style' => 'currency']),
        ];

        $books = [];
        /**
         * @var ReportKKM[] $records
         */
        $records = $this->get('doctrine_mongodb')->getRepository(ReportKKM::class)
            ->findByPeriod($datePeriodStart, $datePeriodEnd);
        foreach ($records as $record) {
            $dataPost = $record->getDataPost();
            $orderSum = $dataPost['kkm']['payment']['cash'] ?? 0 + $dataPost['kkm']['payment']['card'] ?? 0;

            $books[] = [
                'date' => $record->getDatetime(),
                'typePayment' => $record->getTypePayment(),
                'INN' => $record->getInn(),
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
}