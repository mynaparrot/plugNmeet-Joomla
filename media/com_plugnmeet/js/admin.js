window.Joomla = window.Joomla || {};

(function (window, document, Joomla) {
    'use strict';

    Joomla.toggleField = function (id, task, field) {

        var f = document.adminForm, i = 0, cbx, cb = f[id];

        if (!cb) return false;

        while (true) {
            cbx = f['cb' + i];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField = document.createElement('input');

        inputField.type = 'hidden';
        inputField.name = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        Joomla.submitform(task);

        return false;
    };

    const AI = 'jform[insights_features][ai_agent_features]';
    const MODE = '[name="jform[room_mode]"]';
    const HIDDEN_TABS = ['roomFeatures', 'otherFeatures', 'lockSettings'];
    const ACTIVE_TAB_KEY = 'com_plugnmeet.room.activeTab';

    let options = {};
    let i18n = {};
    let presets = {};

    const aiField = (name) => document.querySelector('[name="' + AI + '[' + name + ']"]');

    const aiRow = (element) => (element ? element.closest('.control-group') || element.closest('.mb-3') || element.parentElement : null);

    const setRowVisible = (row, visible) => {
        if (!row) {
            return;
        }

        row.classList.toggle('d-none', !visible);
    };

    const toggleRow = (element, visible) => {
        setRowVisible(aiRow(element), visible);
    };

    const rowHasField = (row, name) => {
        const field = aiField(name);

        return !!field && !!row && row.contains(field);
    };

    const tabButtons = (id) => Array.prototype.slice.call(
        document.querySelectorAll(
            'button[aria-controls="' + id + '"], a[href="#' + id + '"], .nav-link[href="#' + id + '"]'
        )
    );

    const currentRoomMode = () => {
        const input = document.querySelector(MODE + ':checked') || document.querySelector(MODE);

        return input && input.value === 'ai_agent' ? 'ai_agent' : 'conference';
    };

    const roomTabIds = () => Array.prototype.slice
        .call(document.querySelectorAll('#roomTab > joomla-tab-element'))
        .map((tab) => tab.id);

    const readActiveTab = () => {
        try {
            return window.sessionStorage.getItem(ACTIVE_TAB_KEY) || '';
        } catch (e) {
            return '';
        }
    };

    const rememberActiveTab = (event) => {
        const button = event.target;
        const id = button && button.getAttribute ? button.getAttribute('aria-controls') : '';

        if (!id || roomTabIds().indexOf(id) === -1) {
            return;
        }

        try {
            window.sessionStorage.setItem(ACTIVE_TAB_KEY, id);
        } catch (e) {
            // Storage may be unavailable: tab recall is a nice to have.
        }
    };

    /**
     * Joomla's own "recall" keeps the tab in sessionStorage keyed by the current URL,
     * so the selection is lost whenever a save/apply/validation error redirects to a
     * different URL (task=room.edit -> view=room&layout=edit). Keep a copy under a
     * fixed key so the active tab survives those redirects.
     */
    const restoreActiveTab = () => {
        const stored = readActiveTab();

        if (!stored || roomTabIds().indexOf(stored) === -1) {
            return;
        }

        const buttons = tabButtons(stored);

        if (!buttons.length) {
            return;
        }

        const button = buttons[0];
        const active = button.getAttribute('aria-selected') === 'true' || button.classList.contains('active');

        if (active || button.classList.contains('d-none')) {
            return;
        }

        button.click();
    };

    const updateActivityRuleQuotas = () => {
        const map = {
            'allow_whiteboard_write': 'activity_rules][min_whiteboard_drawings',
            'allow_polls': 'activity_rules][min_quizzes',
            'allow_notepad_write': 'activity_rules][min_notepad_entries',
        };

        Object.keys(map).forEach((tool) => {
            const toolField = aiField(tool);
            const quota = aiField(map[tool]);

            if (toolField && quota) {
                quota.disabled = toolField.value === '0';
            }
        });
    };

    /**
     * Mirrors WordPress updateInsightsCards(): every visibility rule for the AI
     * agent card lives here so Joomla's showon.js never competes with it.
     */
    const updateInsightsCards = () => {
        const card = document.getElementById('pnm-ai-agent-card');

        if (!card) {
            return;
        }

        const aiMode = currentRoomMode() === 'ai_agent';
        const isAllow = aiField('is_allow');
        const sessionMode = aiField('session_mode');
        const maxConcurrent = aiField('auto_session_config][max_concurrent_sessions');
        const agentOn = aiMode || (!!isAllow && isAllow.value === '1');
        const controlled = aiMode && !!sessionMode && sessionMode.value === '1';

        const standardCard = document.getElementById('pnm-insights-standard-card');

        if (standardCard) {
            standardCard.classList.toggle('d-none', aiMode);
        }

        setRowVisible(aiRow(isAllow), !aiMode);
        setRowVisible(aiRow(sessionMode), aiMode);
        setRowVisible(aiRow(maxConcurrent), aiMode && !controlled);

        const allowedParticipants = document.querySelector('[name="' + AI + '[auto_session_config][allowed_participant_ids][]"], [id^="jform_insights_features_ai_agent_features_auto_session_config_allowed_participant_ids"]');
        const knownRows = [aiRow(isAllow), aiRow(sessionMode), aiRow(maxConcurrent), aiRow(allowedParticipants)];

        setRowVisible(aiRow(allowedParticipants), controlled);

        // Every other row (including the section headings) is a detail row.
        Array.prototype.forEach.call(card.querySelectorAll('.control-group'), (row) => {
            if (knownRows.indexOf(row) !== -1) {
                return;
            }

            let visible = agentOn;

            if ((rowHasField(row, 'avatar][is_enabled') || rowHasField(row, 'avatar][max_avatar_minutes')) && !options.avatarEnabledGlobal) {
                visible = false;
            }

            if ((rowHasField(row, 'allow_web_fetch') || rowHasField(row, 'allowed_knowledge_urls')) && !options.allowWebFetchGlobal) {
                visible = false;
            }

            if (rowHasField(row, 'enable_mcp_servers') && !options.hasMcpServers) {
                visible = false;
            }

            if (visible && rowHasField(row, 'allowed_knowledge_urls')) {
                const webFetch = aiField('allow_web_fetch');

                visible = !!webFetch && webFetch.value === '1';
            }

            if (visible && rowHasField(row, 'avatar][max_avatar_minutes')) {
                const avatarEnabled = aiField('avatar][is_enabled');

                visible = !!avatarEnabled && avatarEnabled.value === '1';
            }

            setRowVisible(row, visible);
        });

        updateActivityRuleQuotas();
    };

    const setTabVisible = (id, visible) => {
        tabButtons(id).forEach((button) => {
            button.classList.toggle('d-none', !visible);
        });
    };

    const updateRoomModeUi = () => {
        if (!document.getElementById('room-form')) {
            return;
        }

        const aiMode = currentRoomMode() === 'ai_agent';

        document.querySelectorAll('.pnm-mode-option').forEach((label) => {
            const radio = label.querySelector('input[type="radio"]');
            label.classList.toggle('is-active', !!(radio && radio.checked));
        });

        HIDDEN_TABS.forEach((id) => {
            if (aiMode) {
                const active = tabButtons(id).filter(
                    (button) => button.getAttribute('aria-selected') === 'true' || button.classList.contains('active')
                );

                if (active.length) {
                    const basic = tabButtons('basic');

                    if (basic.length) {
                        basic[0].click();
                    }
                }
            }

            setTabVisible(id, !aiMode);
        });

        toggleRow(document.getElementById('jform_max_participants'), !aiMode);

        tabButtons('insightsFeatures').forEach((button) => {
            button.textContent = aiMode ? i18n.ai_agent_tab : i18n.insights_tab;
        });

        // The AI agent card is controlled by the room mode switcher only: is_allow keeps
        // its conference-mode value and is stored as 1 by RoomModel::save() in ai_agent mode.
        updateInsightsCards();
    };

    const applyPreset = (select) => {
        const prompt = aiField('system_prompt');
        const preset = presets[select.value];

        if (!prompt || typeof preset !== 'string' || preset === '') {
            return;
        }

        const known = Object.keys(presets).map((key) => String(presets[key]).trim());

        if (String(prompt.value).trim() !== '' && known.indexOf(String(prompt.value).trim()) === -1 && !window.confirm(i18n.replace_prompt)) {
            return;
        }

        prompt.value = preset;

        ['allow_whiteboard_write', 'allow_vision_capture', 'allow_polls', 'allow_notepad_write', 'allow_chat_message'].forEach((name) => {
            const element = aiField(name);

            if (element) {
                element.value = '1';
            }
        });

        updateActivityRuleQuotas();
    };

    const init = () => {
        options = Joomla.getOptions ? Joomla.getOptions('plugnmeet.admin', {}) : {};
        i18n = options.i18n || {};
        presets = options.aiPresets || {};

        if (!document.getElementById('room-form')) {
            return;
        }

        updateRoomModeUi();

        document.addEventListener('change', (event) => {
            const target = event.target;

            if (!target || !target.name) {
                return;
            }

            if (target.name === 'jform[room_mode]') {
                updateRoomModeUi();
            } else if (target.name === AI + '[session_mode]'
                || target.name === AI + '[is_allow]'
                || target.name === AI + '[allow_web_fetch]'
                || target.name === AI + '[avatar][is_enabled]') {
                updateInsightsCards();
            } else if (target.name === AI + '[preset_template]') {
                applyPreset(target);
            } else if (/\[allow_(whiteboard_write|polls|notepad_write)\]$/.test(target.name)) {
                updateActivityRuleQuotas();
            }
        });

        updateActivityRuleQuotas();

        document.addEventListener('joomla.tab.shown', rememberActiveTab);

        // The tab set is a custom element: its buttons only exist after
        // connectedCallback + requestAnimationFrame, so re-apply the room mode
        // UI (tab hiding/renaming) once it has hydrated.
        const reapplyRoomModeUi = () => window.requestAnimationFrame(() => {
            restoreActiveTab();
            updateRoomModeUi();
        });

        if (window.customElements && window.customElements.whenDefined) {
            window.customElements.whenDefined('joomla-tab')
                .then(reapplyRoomModeUi)
                .catch(() => {});
        }

        window.addEventListener('load', reapplyRoomModeUi);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document, window.Joomla);
