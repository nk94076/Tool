<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EmailTemplate;
use App\Services\AuditService;

final class EmailTemplateController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('admin/email_templates_index', [
            'title' => 'Email Templates',
            'templates' => (new EmailTemplate())->all('name'),
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireLogin();
        $template = (new EmailTemplate())->find((int) $params['id']);
        if (!$template) {
            (new \App\Core\Router())->abort(404);
        }
        $this->view('admin/email_template_edit', [
            'title' => 'Edit Template: ' . $template['name'],
            'template' => $template,
        ]);
    }

    public function update(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new EmailTemplate();
        $template = $model->find((int) $params['id']);
        if (!$template) {
            (new \App\Core\Router())->abort(404);
        }

        $subject = trim((string) $this->input('subject', ''));
        $bodyHtml = (string) $this->input('body_html', '');
        $isActive = $this->input('is_active', '1') === '1' ? 1 : 0;

        if ($subject === '' || $bodyHtml === '') {
            set_flash('error', 'Subject and body are required.');
            $this->redirect('/admin/email-templates/' . $template['id'] . '/edit');
        }

        $model->update((int) $template['id'], [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'is_active' => $isActive,
            'updated_by' => \App\Core\Auth::id(),
        ]);

        AuditService::log('email_template.updated', null, 'template:' . $template['slug']);
        set_flash('success', 'Template updated.');
        $this->redirect('/admin/email-templates');
    }
}
