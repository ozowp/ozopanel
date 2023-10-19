<?php
namespace WAM\Helper;
/**
 * All the preset data
 *
 * @since 1.0.0
 */
class Preset
{
    public function data()
    {
        return [
            'email_template' => [
                'estimate' => [
                    'default' => [
                        'subject' => '{org_name} sent you a Estimate #{id}',
                        'msg' => 'Hi <b>{client_name}</b>,
Please find attached Estimate #{id}. Due Date is {due_date}.

Estimate No: #{id}
Estimate Date: {date}
Due Date: {due_date}
Amount: {amount}

Thank you for your business.

Regards
{org_name}'
                    ],
                    'reminder' => [
                        'subject' => '{org_name} sent you a reminder of Estimate #{id}',
                        'msg' => 'Hi <b>{client_name}</b>,
Please find attached Estimate #{id}. Due Date was {due_date}.

Estimate No: #{id}
Estimate Date: {date}
Due Date: {due_date}
Amount: {amount}

Thank you for your business.

Regards
{org_name}'
                    ]
                ],
                'invoice' => [
                    'default' => [
                        'subject' => '{org_name} sent you a Invoice #{id}',
                        'msg' => 'Hi <b>{client_name}</b>,
Please find attached Invoice #{id}. Due Date is {due_date}.

Invoice No: #{id}
Invoice Date: {date}
Due Date: {due_date}
Due Amount: {amount}

Thank you for your business.

Regards
{org_name}'
                    ],
                    'reminder' => [
                        'subject' => '{org_name} sent you a reminder of Invoice #{id}',
                        'msg' => 'Hi <b>{client_name}</b>,
Please find attached Invoice #{id}. Due Date was {due_date}.

Invoice No: #{id}
Invoice Date: {date}
Due Date: {due_date}
Due Amount: {amount}

Thank you for your business.

Regards
{org_name}'
                    ],
                    'recurring' => [
                        'subject' => '{org_name} sent you a recurring of Invoice #{id}',
                        'msg' => 'Hi <b>{client_name}</b>,
Please find attached Invoice #{id}. Due Date was {due_date}.

Invoice No: #{id}
Invoice Date: {date}
Due Date: {due_date}
Due Amount: {amount}

Thank you for your business.

Regards
{org_name}'
                    ]
                ],    
            ]
        ];
    }
}
