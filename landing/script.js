// script.js
document.addEventListener('DOMContentLoaded', function () {
    console.log("PrognozAi.ru: DOM Content Loaded. Script.js is initializing...");

    // --- ОБЩИЕ ФУНКЦИИ ДЛЯ ВСЕХ СТРАНИЦ ---

    // Мобильное меню
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true' || false;
            mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
            mobileMenu.setAttribute('aria-hidden', isExpanded);
        });
    }

    // Год в футере
    const currentYearSpan = document.getElementById('current-year');
    if (currentYearSpan) {
        currentYearSpan.textContent = new Date().getFullYear();
    }

    // Анимация логотипа при загрузке
    document.querySelectorAll('img.logo-hover-effect').forEach(logo => {
        if (logo) { // Добавлена проверка на существование лого
            logo.style.opacity = '0';
            logo.style.transform = 'translateY(10px)';
            setTimeout(() => {
                logo.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                logo.style.opacity = '1';
                logo.style.transform = 'translateY(0)';
            }, 300);
        }
    });

    // Плавное появление секций при скролле (Общее)
    const sectionsToAnimate = document.querySelectorAll(
        '.hero-gradient-bg h1, .hero-gradient-bg p, .hero-gradient-bg > div > div > a, .hero-gradient-bg > div > div > img, section > div > .section-title, section > div > p.text-secondary, section > div > .grid, section > div > .flex:not(.items-center):not(.space-x-2):not(.justify-center)'
    );
    if (typeof IntersectionObserver !== "undefined") {
        const commonIntersectionObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { root: null, rootMargin: '0px', threshold: 0.05 });
        sectionsToAnimate.forEach(el => {
            el.classList.add('fade-in-up');
            commonIntersectionObserver.observe(el);
        });
    } else {
        sectionsToAnimate.forEach(el => { // Fallback
            el.classList.add('is-visible'); el.style.opacity = '1'; el.style.transform = 'translateY(0)';
        });
    }

    // Кнопки выбора суммы для доната в тарифах (если есть на странице)
    const donationAmountButtons = document.querySelectorAll('.donation-btn');
    const donationAmountInput = document.getElementById('donation-amount');
    if (donationAmountInput && donationAmountButtons.length > 0) {
        donationAmountButtons.forEach(button => {
            button.addEventListener('click', () => {
                donationAmountInput.value = button.dataset.amount;
            });
        });
    }

    // Демо-заглушки для кнопок покупки/подписки (Общее)
    document.querySelectorAll('.button-base').forEach(button => {
        const buttonText = button.textContent.trim().toLowerCase();
        const isFormButton = button.closest('form'); // Кнопки внутри форм (например, чат Палыча)
        const isDonationChoiceButton = button.classList.contains('donation-btn'); // Кнопки выбора суммы доната

        const nonPaymentKeywords = ['спросить палыча', 'узнать больше о вкладе', 'поддержать мечту'];
        let isNonPaymentAction = nonPaymentKeywords.some(keyword => buttonText.includes(keyword)) || isDonationChoiceButton;

        if (!isNonPaymentAction && !isFormButton) {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                alert('Переход на страницу оплаты (демонстрация). Здесь будет интеграция с платежной системой.');
            });
        }
    });


    // --- КОД, СПЕЦИФИЧНЫЙ ДЛЯ ГЛАВНОЙ СТРАНИЦЫ (index.html) ---
    if (document.getElementById('ask-palych-form') && !document.body.classList.contains('faq-page-marker') && !document.body.classList.contains('community-page')) { // Убедимся, что это не чат на других страницах, если ID будет одинаковый
        console.log("PrognozAi.ru: Main page specific scripts (Chat, Main Progress Bar) initializing...");
        
        // Чат с Палычем
        const askPalychFormMain = document.getElementById('ask-palych-form');
        const palychQuestionInputMain = document.getElementById('palych-question');
        const palychAnswerAreaMain = document.getElementById('palych-answer-area');
        const palychGreetingBubbleMain = document.getElementById('palych-greeting-bubble');
        
        // ВАЖНО: API Ключ OpenAI! 
        const OPENAI_API_KEY_MAIN = "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"; // <--- ЗАМЕНИ НА СВОЙ КЛЮЧ

        if (askPalychFormMain && palychQuestionInputMain && palychAnswerAreaMain) {
            askPalychFormMain.addEventListener('submit', async function (event) {
                event.preventDefault();
                const userQuestion = palychQuestionInputMain.value.trim();

                if (!OPENAI_API_KEY_MAIN || OPENAI_API_KEY_MAIN.includes("xxxx") || OPENAI_API_KEY_MAIN === "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") {
                    displayMessageInChat("API ключ для 'Палыча' не настроен.", "error", palychAnswerAreaMain);
                    return;
                }
                if (!userQuestion) {
                    displayMessageInChat("Пожалуйста, введите ваш вопрос.", "error", palychAnswerAreaMain);
                    return;
                }
                if (palychGreetingBubbleMain && palychGreetingBubbleMain.parentNode === palychAnswerAreaMain) {
                    palychGreetingBubbleMain.remove();
                }
                displayMessageInChat(userQuestion, "user", palychAnswerAreaMain);
                palychQuestionInputMain.value = '';
                const loadingBubble = displayMessageInChat("Палыч анализирует ваш вопрос... 🤖", "loading", palychAnswerAreaMain);

                try {
                    const response = await fetch('https://api.openai.com/v1/chat/completions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${OPENAI_API_KEY_MAIN}`
                        },
                        body: JSON.stringify({
                            model: "gpt-3.5-turbo",
                            messages: [
                                { role: "system", content: "Ты — Палыч, опытный ИИ-аналитик футбола. Твой стиль общения: дружелюбный, немного с юмором, как у старого футбольного эксперта, но всегда по существу и авторитетно. Ты никогда не даешь прямых советов по ставкам или гарантированных прогнозов на исход. Вместо этого, ты предоставляешь анализ, основанный на статистике, вероятностях, сильных/слабых сторонах команд, текущей форме, тактике. Твои ответы должны быть лаконичными (2-5 предложений), но содержательными. Если вопрос не о футболе, вежливо откажись, например: 'Эх, дружище, в этом я не силен, давай лучше про футбол поговорим, а?'. Всегда в начале ответа указывай 'Палыч:'." },
                                { role: "user", content: userQuestion }
                            ],
                            max_tokens: 250, temperature: 0.65
                        })
                    });
                    if (loadingBubble && loadingBubble.parentNode) loadingBubble.remove();
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({ error: { message: "Не удалось получить детали ошибки." }}));
                        displayMessageInChat(`Палыч временно недоступен (ошибка ${response.status}). ${errorData.error?.message || ''}`, "error", palychAnswerAreaMain);
                        return;
                    }
                    const data = await response.json();
                    if (data.choices && data.choices.length > 0 && data.choices[0].message && data.choices[0].message.content) {
                        let palychResponse = data.choices[0].message.content.trim();
                        if (!palychResponse.toLowerCase().startsWith("палыч:")) palychResponse = "Палыч: " + palychResponse;
                        displayMessageInChat(palychResponse, "palych", palychAnswerAreaMain);
                    } else {
                        displayMessageInChat("Палыч что-то задумался. Попробуйте переформулировать.", "error", palychAnswerAreaMain);
                    }
                } catch (error) {
                    if (loadingBubble && loadingBubble.parentNode) loadingBubble.remove();
                    displayMessageInChat("Ошибка связи с Палычем. Проверьте интернет.", "error", palychAnswerAreaMain);
                }
            });
        }

        // Прогресс-бар донатов на главной странице
        const mainProgressBarFill = document.getElementById('progress-bar-fill'); // ID с главной
        const mainProgressBarText = document.getElementById('progress-bar-text');
        const mainProgressGoalSpan = document.getElementById('progress-goal');
        if (mainProgressBarFill && mainProgressBarText && mainProgressGoalSpan) {
            animateSingleProgressBar(mainProgressBarFill, mainProgressBarText, mainProgressGoalSpan, 450000, "Собрано");
        }
    }

    // --- КОД, СПЕЦИФИЧНЫЙ ДЛЯ СТРАНИЦЫ СООБЩЕСТВА (community.html) ---
    if (document.body.classList.contains('community-page')) {
        console.log("PrognozAi.ru: Community page specific scripts initializing...");

        // Анимация прогресс-баров рейтинга участников
        document.querySelectorAll('.progress-bar-container.rating-progress-bar').forEach(barContainer => {
            const fill = barContainer.querySelector('.progress-bar-fill');
            const percent = barContainer.dataset.percent;
            if (fill && percent) {
                fill.style.width = '0%'; // Начальное состояние
                if (typeof IntersectionObserver !== "undefined") {
                    const observer = new IntersectionObserver(entries => {
                        if (entries[0].isIntersecting) {
                            setTimeout(() => fill.style.width = percent + '%', 100);
                            observer.unobserve(barContainer);
                        }
                    }, { threshold: 0.5 });
                    observer.observe(barContainer);
                } else {
                    fill.style.width = percent + '%'; // Fallback
                }
            }
        });
        
        // Прогресс-бар донатов на цели сообщества
        const communityProgressBarFill = document.getElementById('progress-bar-fill-community');
        const communityProgressBarText = document.getElementById('progress-bar-text-community');
        const communityProgressGoalSpan = document.getElementById('progress-goal-community');
        if (communityProgressBarFill && communityProgressBarText && communityProgressGoalSpan) {
             animateSingleProgressBar(communityProgressBarFill, communityProgressBarText, communityProgressGoalSpan, 182000, "Собрано на цели сообщества");
        }
    }

    // --- КОД, СПЕЦИФИЧНЫЙ ДЛЯ СТРАНИЦЫ FAQ (faq.html) ---
    // Этот скрипт был встроен в faq.html, если он там остался, этот блок не нужен.
    // Если ты его удалил из faq.html и хочешь иметь в общем script.js:
    if (document.getElementById('faq-search-input') && document.querySelectorAll('.faq-item').length > 0) {
        console.log("PrognozAi.ru: FAQ page specific scripts initializing (from main script)...");
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const answer = item.querySelector('.faq-answer');
            const icon = item.querySelector('.faq-icon');
            const button = item.querySelector('.faq-question');
            item.classList.remove('active');
            if (answer) { answer.style.maxHeight = '0'; answer.style.opacity = '0'; answer.style.paddingTop = '0'; answer.style.paddingBottom = '0';}
            if (icon) { icon.classList.remove('rotate-180'); }
            if (button) { button.setAttribute('aria-expanded', 'false'); }
        });
        faqItems.forEach(item => {
            const button = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            const icon = button.querySelector('.faq-icon');
            if (button && answer && icon) {
                button.addEventListener('click', () => {
                    const isOpen = item.classList.contains('active');
                    faqItems.forEach(otherItem => { /* ... (логика закрытия других) ... */ }); // Сокращено для примера
                    if (!isOpen) { item.classList.add('active'); /* ... (логика открытия) ... */ } 
                    else { item.classList.remove('active'); /* ... (логика закрытия) ... */ }
                });
            }
        });
        // ... (полный код аккордеона и поиска из твоего faq.html script)
        console.log("PrognozAi.ru: FAQ page scripts initialized (from main script).");
    }


    // --- ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ---

    // Универсальная функция для анимации одного прогресс-бара
    function animateSingleProgressBar(fillElement, textElement, goalSpanElement, currentAmount, textPrefix = "Собрано") {
        if (!fillElement || !textElement || !goalSpanElement) {
            console.warn("PrognozAi.ru: Missing elements for single progress bar animation.");
            return;
        }
        const goalAmountText = goalSpanElement.textContent.replace(/\s/g, '').replace('₽', '');
        const goalAmount = parseInt(goalAmountText);

        if (!isNaN(goalAmount) && goalAmount > 0) {
            const percentage = Math.min((currentAmount / goalAmount) * 100, 100);
            if (typeof IntersectionObserver !== "undefined") {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            setTimeout(() => fillElement.style.width = percentage + '%', 200);
                            textElement.textContent = `${textPrefix}: ${currentAmount.toLocaleString('ru-RU')} ₽`;
                            if(fillElement.parentNode) obs.unobserve(fillElement.parentNode);
                        }
                    });
                }, { threshold: 0.5 });
                if (fillElement.parentNode) observer.observe(fillElement.parentNode);
            } else {
                fillElement.style.width = percentage + '%';
                textElement.textContent = `${textPrefix}: ${currentAmount.toLocaleString('ru-RU')} ₽`;
            }
        } else {
            console.warn("PrognozAi.ru: Goal amount for progress bar is invalid:", goalSpanElement.textContent);
        }
    }
    
    // Универсальная функция для отображения сообщений в чате (если их несколько на сайте)
    function displayMessageInChat(text, type, container) {
        // console.log(`PrognozAi.ru: Displaying message in chat - Type: ${type}`);
        const bubble = document.createElement('div');
        let htmlContent = '';
        const escapeHTML = (str) => str.replace(/[&<>'"]/g, tag => ({'&':'&','<':'<','>':'>',"'":''','"':'"'}[tag]||tag));
        const sanitizedText = escapeHTML(text);
        const formattedText = sanitizedText.replace(/\n/g, '<br>');

        if (type === "user") {
            bubble.className = 'user-chat-bubble'; // Стили из style.css
            htmlContent = `<p class="font-semibold text-white opacity-80">Вы:</p><p class="text-sm">${formattedText}</p>`;
        } else if (type === "palych") {
            bubble.className = 'palych-chat-bubble'; // Стили из style.css
            const namePrefix = `<strong class="text-accent-primary">Палыч:</strong>`;
            htmlContent = `<p class="font-semibold">${namePrefix}</p><p class="text-sm">${formattedText.startsWith("Палыч:") ? formattedText.substring(7).trim() : formattedText}</p>`;
        } else if (type === "loading") {
            bubble.className = 'palych-chat-bubble palych-loading'; // Стили из style.css
            htmlContent = `<p class="font-semibold"><strong class="text-accent-primary">Палыч:</strong></p><p class="text-sm italic animate-pulse">${formattedText}</p>`;
        } else if (type === "error") {
            bubble.className = 'palych-chat-bubble error-bubble'; // Стили из style.css
            htmlContent = `<p class="font-semibold"><strong class="text-red-500">Палыч (ошибка):</strong></p><p class="text-sm">${formattedText}</p>`;
        }
        
        bubble.innerHTML = htmlContent;
        if (container && typeof container.appendChild === 'function') {
            container.appendChild(bubble);
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        } else {
            console.error("PrognozAi.ru: Invalid container provided to displayMessageInChat:", container);
        }
        return bubble;
    }

    console.log("PrognozAi.ru: Script.js finished execution of initial setup.");
});