import { createRoot } from '@wordpress/element';
import { Panel, PanelBody, PanelRow, Button, Card, CardBody, CardHeader, CardFooter, __experimentalText as Text, ExternalLink } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';

const Dashboard = () => {
    const [stats, setStats] = useState({ endpoints: 0, logs: 0 });
    const [loading, setLoading] = useState(true);

    // Simulated data fetch
    useEffect(() => {
        // In a real app, you'd fetch from REST API
        // apiFetch({ path: '/prograde-oort/v1/stats' }).then(...)
        setTimeout(() => {
            setStats({ endpoints: 3, logs: 154 });
            setLoading(false);
        }, 1000);
    }, []);

    const sections = [
        {
            title: __('Endpoints', 'prograde-oort'),
            description: __('Manage your API routes and automation workflows.', 'prograde-oort'),
            action: __('View Endpoints', 'prograde-oort'),
            href: 'edit.php?post_type=oort_endpoint',
            primary: true
        },
        {
            title: __('Logs', 'prograde-oort'),
            description: __('Monitor webhook activity and execution logs.', 'prograde-oort'),
            action: __('View Logs', 'prograde-oort'),
            href: 'admin.php?page=oort-logs',
            primary: false
        },
        {
            title: __('Import/Export', 'prograde-oort'),
            description: __('Migrate configurations across environments.', 'prograde-oort'),
            action: __('Manage', 'prograde-oort'),
            href: 'admin.php?page=oort-portability',
            primary: false
        }
    ];

    return (
        <div className="oort-dashboard-wrapper" style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
            <div className="oort-hero" style={{ 
                background: 'linear-gradient(135deg, #2271b1 0%, #135e96 100%)', 
                color: 'white', 
                padding: '40px', 
                borderRadius: '8px', 
                marginBottom: '20px', 
                boxShadow: '0 4px 12px rgba(0,0,0,0.1)' 
            }}>
                <h1 style={{ color: 'white', margin: 0, fontSize: '2.5em' }}>{__('Welcome to Prograde Oort', 'prograde-oort')}</h1>
                <p style={{ fontSize: '1.25em', opacity: 0.9, marginBottom: '30px', maxWidth: '800px' }}>
                    {__('The unified automation engine for WordPress. Connect webhooks, process data feeds, and transform content with ease.', 'prograde-oort')}
                </p>
                <div style={{ display: 'flex', gap: '10px' }}>
                    <Button variant="secondary" href="post-new.php?post_type=oort_endpoint" style={{ fontSize: '1.1em', padding: '10px 24px', height: 'auto' }}>
                        {__('🚀 Create Your First Endpoint', 'prograde-oort')}
                    </Button>
                    <Button variant="secondary" href="https://example.com/docs" target="_blank" style={{ fontSize: '1.1em', padding: '10px 24px', height: 'auto', background: 'transparent', color: 'white', border: '1px solid white' }}>
                        {__('📚 Documentation', 'prograde-oort')}
                    </Button>
                </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '20px' }}>
                {sections.map((section, index) => (
                    <Card key={index}>
                        <CardHeader>
                            <Text variant="title.medium" as="h2">{section.title}</Text>
                        </CardHeader>
                        <CardBody>
                            <p>{section.description}</p>
                        </CardBody>
                        <CardFooter>
                            <Button variant={section.primary ? 'primary' : 'secondary'} href={section.href}>
                                {section.action}
                            </Button>
                        </CardFooter>
                    </Card>
                ))}

                <Card>
                    <CardHeader>
                        <Text variant="title.medium" as="h2">{__('System Settings', 'prograde-oort')}</Text>
                    </CardHeader>
                    <CardBody>
                        <form method="post" action="options.php">
                            {/* Note: React form usage in WP Admin requires handling hidden fields correctly or using API settings */}
                            <p>{__('Quick Toggle: Legacy PHP Evaluation', 'prograde-oort')}</p>
                            {/* For full settings, we usually rely on the PHP settings form or AJAX saves */}
                            <Button href="admin.php?page=prograde-oort" variant="tertiary">{__('Go to Full Settings', 'prograde-oort')}</Button>
                        </form>
                    </CardBody>
                </Card>
            </div>
        </div>
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const rootEl = document.getElementById('oort-dashboard-root');
    if (rootEl) {
        const root = createRoot(rootEl);
        root.render(<Dashboard />);
    }
});
