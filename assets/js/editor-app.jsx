import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import OortCodeEditor from './components/OortCodeEditor';

/**
 * Main React app for Oort endpoint editing
 */
const OortEditorApp = () => {
    const [code, setCode] = useState('');
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        // Load initial code from hidden textarea (WordPress metabox pattern)
        const textarea = document.getElementById('oort_logic_code');
        if (textarea) {
            setCode(textarea.value || '<?php\n// Write your custom logic here\n// Available variables: $params, $data\n\nreturn ["status" => "success"];\n');
        }
    }, []);

    const handleChange = (newValue) => {
        setCode(newValue);
        // Sync with WordPress hidden field
        const textarea = document.getElementById('oort_logic_code');
        if (textarea) {
            textarea.value = newValue;
        }
    };

    return (
        <div className="oort-editor-container">
            <div className="oort-editor-header">
                <h3>Custom PHP Logic</h3>
                <div className="oort-editor-hints">
                    <span className="hint">💡 Use <code>$params</code> for webhook data</span>
                    <span className="hint">📦 Action Scheduler: <code>as_enqueue_async_action()</code></span>
                    <span className="hint">🔌 HTTP Client: <code>GuzzleHttp\Client</code></span>
                </div>
            </div>
            <OortCodeEditor
                value={code}
                onChange={handleChange}
                height="500px"
            />
            <input
                type="hidden"
                id="oort_logic_code"
                name="oort_logic_code"
                value={code}
            />
        </div>
    );
};

// Initialize React app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const editorRoot = document.getElementById('oort-react-editor-root');
    if (editorRoot) {
        const root = createRoot(editorRoot);
        root.render(<OortEditorApp />);
    }
});
