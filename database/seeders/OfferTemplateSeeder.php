<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OfferTemplate;

class OfferTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Standard Employment Offer',
                'content' => '
                    <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                        <h2 style="color: #4f46e5;">Offer of Employment</h2>
                        <p>Dear <strong>{{candidate_name}}</strong>,</p>
                        <p>We are delighted to offer you the position of <strong>{{designation}}</strong> at our company. We were impressed with your skills and background and believe you will be a valuable addition to our team.</p>
                        <p><strong>Compensation Package:</strong></p>
                        <ul>
                            <li>Annual Salary: <strong>{{salary_amount}}</strong></li>
                            <li>Start Date: <strong>{{joining_date}}</strong></li>
                        </ul>
                        <p>We look forward to welcoming you aboard.</p>
                        <p>Sincerely,<br>Talent Acquisition Team</p>
                    </div>'
            ],
            [
                'name' => 'Senior / Executive Offer',
                'content' => '
                    <div style="font-family: Georgia, serif; line-height: 1.8; color: #1a1a1a;">
                        <h1 style="border-bottom: 2px solid #333; padding-bottom: 10px;">Executive Offer Letter</h1>
                        <p>Dear {{candidate_name}},</p>
                        <p>It is my pleasure to extend the following offer of employment to you for the role of <strong>{{designation}}</strong>.</p>
                        <p>Your leadership and expertise are exactly what we need to drive our vision forward.</p>
                        <div style="background: #f9fafb; padding: 20px; border-left: 4px solid #333; margin: 20px 0;">
                            <p style="margin: 0;"><strong>Annual Compensation:</strong> {{salary_amount}}</p>
                            <p style="margin: 5px 0 0;"><strong>Date of Joining:</strong> {{joining_date}}</p>
                        </div>
                        <p>This offer is contingent upon the successful completion of background checks.</p>
                        <p>Best Regards,<br>HR Director</p>
                    </div>'
            ],
            [
                'name' => 'Remote Employment Offer',
                'content' => '
                    <div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; color: #2d3748;">
                        <h2 style="color: #2b6cb0; border-bottom: 2px solid #cbd5e0; padding-bottom: 10px;">Remote Employment Offer</h2>
                        <p>Dear <strong>{{candidate_name}}</strong>,</p>
                        <p>We are excited to offer you the full-time, remote position of <strong>{{designation}}</strong>.</p>
                        <p>As a remote-first team member, you will enjoy the flexibility of working from your home office while staying connected through our digital workspace.</p>
                        <div style="background-color: #ebf8ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
                            <p style="margin: 5px 0;"><strong>Annual Compensation:</strong> {{salary_amount}}</p>
                            <p style="margin: 5px 0;"><strong>Start Date:</strong> {{joining_date}}</p>
                        </div>
                        <p>We provide a one-time stipend for setting up your home office.</p>
                        <p>Welcome to the future of work!</p>
                        <p>Best,<br>People Operations</p>
                    </div>'
            ],
            [
                'name' => 'Consultant / Contractor Agreement',
                'content' => '
                    <div style="font-family: Times New Roman, Times, serif; color: #000;">
                        <h2 style="text-align: center; text-transform: uppercase; letter-spacing: 2px;">Consultancy Agreement</h2>
                        <hr>
                        <p><strong>Date:</strong> {{joining_date}}</p>
                        <p><strong>To:</strong> {{candidate_name}}</p>
                        <p>This letter serves as a formal agreement for your engagement as a <strong>{{designation}}</strong> (Consultant) with our organization.</p>
                        <p><strong>Terms of Engagement:</strong></p>
                        <ul>
                            <li><strong>Role:</strong> {{designation}}</li>
                            <li><strong>Fee Structure:</strong> {{salary_amount}}</li>
                            <li><strong>Effective Date:</strong> {{joining_date}}</li>
                        </ul>
                        <p>This contract is valid for a period of 12 months, renewable upon mutual agreement.</p>
                        <p>Sincerely,<br>Legal Department</p>
                    </div>'
            ],
            [
                'name' => 'Part-Time Employment Offer',
                'content' => '
                    <div style="font-family: Arial, Helvetica, sans-serif; color: #4a5568;">
                        <h3 style="color: #ed8936;">Part-Time Job Offer</h3>
                        <p>Hello {{candidate_name}},</p>
                        <p>We are pleased to offer you a part-time role as <strong>{{designation}}</strong>.</p>
                        <p>This role requires 20 hours per week. Your compensation will be prorated to <strong>{{salary_amount}}</strong>.</p>
                        <p>We believe this flexible arrangement will be mutually beneficial.</p>
                        <p>Start Date: <strong>{{joining_date}}</strong></p>
                        <p>Regards,<br>Hiring Manager</p>
                    </div>'
            ]
        ];

        foreach ($templates as $tmpl) {
            OfferTemplate::firstOrCreate(
                ['name' => $tmpl['name']],
                [
                    'content' => $tmpl['content'],
                    'is_active' => true
                ]
            );
        }
    }
}
