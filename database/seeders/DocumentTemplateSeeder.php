<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentTemplate;

class DocumentTemplateSeeder extends Seeder
{
    public function run()
    {
        // 1. Standard Offer Letter
        DocumentTemplate::create([
            'name' => 'Full-Time Offer Letter (Standard)',
            'type' => 'offer',
            'is_active' => true,
            'watermark_text' => 'CONFIDENTIAL',
            'layout_config' => ['headerType' => 'html', 'footerType' => 'html', 'watermarkType' => 'text'],
            'header_html' => '<div style="text-align: center; border-bottom: 2px solid #334155; padding-bottom: 10px; margin-bottom: 20px;">
    <h1 style="color: #334155; margin: 0; font-size: 24px;">ACME Corp Solutions</h1>
    <p style="color: #64748b; margin: 5px 0 0; font-size: 12px;">Tech Park, Bangalore, India | +91-9876543210 | hr@acmecorp.com</p>
</div>',
            'footer_html' => '<div style="text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 30px; font-size: 10px; color: #94a3b8;">
    <p>Acme Corp Solutions Pvt Ltd. | CIN: U12345KA2024PTC123456</p>
    <p>This is a computer-generated document. No signature is required.</p>
</div>',
            'pages_data' => [
                [
                    'id' => 1,
                    'title' => 'Offer Details',
                    'content' => '<p>Date: <strong>{{ date }}</strong></p>

<p>To,<br>
<strong>{{ candidate.name }}</strong><br>
{{ candidate.email }}</p>

<h3>Ref: Offer of Employment</h3>

<p>Dear {{ candidate.name }},</p>

<p>We are pleased to extend an offer to you for the position of <strong>{{ candidate.job_title }}</strong> at <strong>{{ company.name }}</strong>. We were impressed with your skills and experience and believe you will be a valuable addition to our team.</p>

<h4>Compensation Details</h4>
<p>Your Total Annual Cost to Company (CTC) will be <strong>{{ salary.ctc }}</strong>. A detailed salary breakup is provided in Annexure A.</p>

<p><strong>Joining Date:</strong> {{ candidate.joining_date }}</p>

<p>Your employment will be governed by the standard terms and conditions of the organization. You will be on a probation period of 6 months from the date of joining.</p>

<p>We look forward to welcoming you to the team. Please sign and return the duplicate copy of this letter as a token of your acceptance.</p>

<br>
<p>Warm Regards,</p>
<p>
    <strong>HR Manager</strong><br>
    {{ company.name }}
</p>'
                ]
            ]
        ]);

        // 2. Internship Offer
         DocumentTemplate::create([
            'name' => 'Internship Offer Letter',
            'type' => 'offer',
            'is_active' => true,
            'watermark_text' => 'INTERNSHIP',
            'layout_config' => ['headerType' => 'html', 'footerType' => 'html', 'watermarkType' => 'text'],
            'header_html' => '<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
    <span style="font-weight: bold; font-size: 20px; color: #4f46e5;">ACME INNOVATIONS</span>
    <span style="font-size: 12px; color: #666;">Future Talent Program</span>
</div>',
            'footer_html' => '<div style="text-align: center; background-color: #f8fafc; padding: 10px; margin-top: 20px; font-size: 11px; color: #666;">
    confidential & property of Acme Innovations.
</div>',
            'pages_data' => [
                [
                    'id' => 1,
                    'title' => 'Internship Offer',
                    'content' => '<p>Date: {{ date }}</p>

<p>Dear <strong>{{ candidate.name }}</strong>,</p>

<p>We are happy to offer you an <strong>Internship</strong> in the <strong>{{ candidate.department }}</strong> department at {{ company.name }}.</p>

<p><strong>Stipend:</strong> {{ salary.net_salary }} per month</p>
<p><strong>Duration:</strong> 6 Months</p>
<p><strong>Start Date:</strong> {{ candidate.joining_date }}</p>

<p>During this internship, you will have the opportunity to work on live projects and learn from our senior engineers. This offer is contingent upon the successful completion of your background verification.</p>

<p>Congratulations and welcome aboard!</p>

<br>
<p>Sincerely,</p>
<p><strong>Talent Acquisition Team</strong></p>'
                ]
            ]
        ]);
        
        // 3. Appointment Letter (Formal)
        DocumentTemplate::create([
            'name' => 'Formal Appointment Letter',
            'type' => 'letter',
            'is_active' => true,
            'watermark_text' => '',
            'layout_config' => ['headerType' => 'html', 'footerType' => 'html', 'watermarkType' => 'text'],
            'header_html' => '<div style="text-align: right; margin-bottom: 20px;">
    <h2 style="margin: 0; color: #1e293b;">APPOINTMENT LETTER</h2>
    <p style="margin: 0; font-size: 12px;">Ref: HR/APPT/{{ candidate.id }}</p>
</div>',
            'pages_data' => [
                [
                    'id' => 1,
                    'title' => 'Page 1',
                    'content' => '<p><strong>PRIVATE & CONFIDENTIAL</strong></p>
            
<p>To,<br>{{ candidate.name }}<br>Employee ID: {{ candidate.id }}</p>

<p><strong>Sub: Appointment Letter for the post of {{ candidate.job_title }}</strong></p>

<p>Dear {{ candidate.name }},</p>

<p>Further to your acceptance of the offer letter, we are pleased to confirm your appointment as <strong>{{ candidate.job_title }}</strong> in our organization, effective from <strong>{{ candidate.joining_date }}</strong>.</p>'
                ]
            ]
        ]);

        // 4. Appraisal / Hike Letter
        DocumentTemplate::create([
            'name' => 'Annual Appraisal Letter',
            'type' => 'appraisal',
            'is_active' => true,
            'watermark_text' => 'CONFIDENTIAL',
            'layout_config' => ['headerType' => 'html', 'footerType' => 'html', 'watermarkType' => 'text'],
            'header_html' => '<div style="border-bottom: 2px solid #4f46e5; padding-bottom: 10px; margin-bottom: 20px;">
    <h2 style="color: #4f46e5; margin: 0;">Annual Compensation Review</h2>
</div>',
            'pages_data' => [
                [
                    'id' => 1,
                    'title' => 'Appraisal Details',
                    'content' => '<p>Date: {{ date }}</p>

<p>To,<br><strong>{{ candidate.name }}</strong></p>

<p>Dear {{ candidate.name }},</p>

<p>We are pleased to inform you that your performance for the last financial year has been reviewed. Based on your significant contributions to the team, the management has decided to revise your compensation.</p>

<p><strong>New Annual CTC:</strong> {{ salary.ctc }}<br>
<strong>Effective Date:</strong> April 1st, {{ year }}</p>

<p>We appreciate your hard work and look forward to your continued success with us.</p>

<br>
<p>Best Regards,</p>
<p><strong>Human Resources</strong></p>'
                ]
            ]
        ]);

        // 5. Warning Letter
        DocumentTemplate::create([
            'name' => 'First Warning Letter (Disciplinary)',
            'type' => 'warning',
            'is_active' => true,
            'watermark_text' => 'WARNING',
            'layout_config' => ['headerType' => 'html', 'footerType' => 'html', 'watermarkType' => 'text'],
            'header_html' => '<div style="text-align: center; color: #dc2626;">
    <h2 style="border: 2px solid #dc2626; display: inline-block; padding: 5px 20px;">STRICTLY CONFIDENTIAL</h2>
</div>',
            'pages_data' => [
                [
                    'id' => 1,
                    'title' => 'Warning Details',
                    'content' => '<p>Date: {{ date }}</p>
<p>To,<br>{{ candidate.name }}</p>

<p><strong>Subject: First Warning Letter for Misconduct</strong></p>

<p>Dear {{ candidate.name }},</p>

<p>It has been brought to our attention that [Reason for Warning]. This behavior is in violation of the company\'s Code of Conduct.</p>

<p>This letter serves as a formal warning. We expect immediate improvement in your conduct. Failure to do so may lead to further disciplinary action, up to and including termination of employment.</p>

<p>Please sign a copy of this letter to acknowledge receipt.</p>

<br>
<p>Authorized Signatory</p>'
                ]
            ]
        ]);

        // 6. Relieving Letter
        DocumentTemplate::create([
            'name' => 'Relieving & Experience Letter',
            'type' => 'relieving',
            'is_active' => true,
            'watermark_text' => '',
            'layout_config' => ['headerType' => 'html', 'footerType' => 'html', 'watermarkType' => 'text'],
            'header_html' => '<div style="text-align: right; margin-bottom: 20px;">
    <h2>TO WHOMSOEVER IT MAY CONCERN</h2>
</div>',
            'pages_data' => [
                [
                    'id' => 1,
                    'title' => 'Experience Certificate',
                    'content' => '<p>Date: {{ date }}</p>

<p>This is to certify that Mr./Ms. <strong>{{ candidate.name }}</strong> was employed with <strong>{{ company.name }}</strong> as <strong>{{ candidate.job_title }}</strong>.</p>

<p><strong>Tenure:</strong> From {{ candidate.joining_date }} to [Last Working Day].</p>

<p>During their tenure, we found them to be sincere and hardworking. We confirm that they have been relieved from their duties effective from the closing hours of [Last Working Day].</p>

<p>We wish them all the best for their future endeavors.</p>

<br>
<p>For {{ company.name }},</p>
<br>
<p>Manager - HR</p>'
                ]
            ]
        ]);

        // 7. Promotion Letter
        DocumentTemplate::create([
            'name' => 'Promotion Letter',
            'type' => 'promotion',
            'is_active' => true,
            'watermark_text' => 'PROMOTION',
             'layout_config' => ['headerType' => 'html', 'footerType' => 'html', 'watermarkType' => 'text'],
            'header_html' => '<div style="text-align: center; border-bottom: 2px solid #16a34a; padding-bottom: 10px; margin-bottom: 20px;">
    <h1 style="color: #16a34a; margin: 0;">Congratulations on Your Promotion!</h1>
</div>',
            'pages_data' => [
                [
                    'id' => 1,
                    'title' => 'Promotion Details',
                    'content' => '<p>Date: {{ date }}</p>

<p>To,<br><strong>{{ candidate.name }}</strong></p>

<p>Dear {{ candidate.name }},</p>

<p>We are delighted to announce your promotion to the position of <strong>{{ candidate.job_title }}</strong>, effective from <strong>{{ candidate.joining_date }}</strong>.</p>

<p>This promotion is a recognition of your hard work, dedication, and leadership. We are confident that you will continue to excel in your new role.</p>

<p><strong>Revised Compensation:</strong> Refer to Annexure A.</p>

<p>We wish you continued success!</p>

<br>
<p>Warm Regards,</p>
<p><strong>CEO / HR Head</strong></p>'
                ]
            ]
        ]);
    }
}
