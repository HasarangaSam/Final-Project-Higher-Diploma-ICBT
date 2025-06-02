<!-- Chat Icon -->
<div id="chatbot-icon">
    <i class="bi bi-chat-dots-fill"></i>
</div>

<!-- Chat Widget -->
<div id="chatbot-widget">
    <div id="chatbot-header">
        <span>Chat Bot</span>
        <i id="close-chatbot" class="fas fa-times"></i>
    </div>
    <div id="chatbot-body"></div>
    <div id="chatbot-footer">
        <input type="text" id="chatbot-user-input" placeholder="Type your message..." onkeypress="handleChatKeyPress(event)">
        <button onclick="sendChatMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
    let userName = sessionStorage.getItem("chatbotUserName") || "";
    const API_KEY = "";  
    const API_URL = "https://api-inference.huggingface.co/models/tiiuae/falcon-7b-instruct";

    // Predefined responses
    const predefinedResponses = {
        "What products do you sell?": "We sell desktops, laptops, gaming accessories, and PC components.",
        "Do you offer repair services?": "Yes! We provide PC/laptop repairs, hardware upgrades, virus removal, and troubleshooting for printers and networks.",
        "Should I buy a desktop or a laptop?": "Get a laptop for portability and convenience; choose a desktop for better performance, upgrades, and longevity.",
        "Do you offer home delivery?": "Yes! We offer fast and secure home delivery for all products.",
        "What are your store hours?": "We’re open Monday–Friday (9 AM – 7 PM), Saturday (10 AM – 6 PM)."
    };

    document.getElementById("chatbot-icon").addEventListener("click", function(event) {
        let chatWidget = document.getElementById("chatbot-widget");
        chatWidget.style.display = "flex";

        if (!userName) {
            setTimeout(startChat, 500);
        } else {
            setTimeout(() => {
                appendChatMessage("bot", `Welcome back, ${userName}! How can I support you today?(Select an option below or type your question)`);
                showChatOptions();
            }, 500);
        }

        event.stopPropagation();
    });

    document.getElementById("close-chatbot").addEventListener("click", function(event) {
        document.getElementById("chatbot-widget").style.display = "none";
        event.stopPropagation();
    });

    document.addEventListener("click", function(event) {
        let chatWidget = document.getElementById("chatbot-widget");
        let chatIcon = document.getElementById("chatbot-icon");

        if (chatWidget.style.display === "flex" && !chatWidget.contains(event.target) && !chatIcon.contains(event.target)) {
            chatWidget.style.display = "none";
        }
    });

    function startChat() {
        appendChatMessage("bot", "Hi... What's your name?");
    }

    function sendChatMessage() {
        let inputField = document.getElementById("chatbot-user-input");
        let text = inputField.value.trim();
        if (text === "") return;

        appendChatMessage("user", text);
        inputField.value = "";

        if (!userName) {
            userName = text;
            sessionStorage.setItem("chatbotUserName", userName);
            setTimeout(() => {
                appendChatMessage("bot", `Nice to meet you, ${userName}! How can I support you today?`);
                showChatOptions();
            }, 1000);
        } else {
            handleUserQuery(text);
        }
    }

    function handleChatKeyPress(event) {
        if (event.key === "Enter") {
            sendChatMessage();
        }
    }

    function appendChatMessage(sender, text) {
        let chatBody = document.getElementById("chatbot-body");
        let messageDiv = document.createElement("div");
        messageDiv.className = `chatbot-message chatbot-${sender}-message`;
        messageDiv.innerText = text;
        chatBody.appendChild(messageDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function showChatOptions() {
        let chatBody = document.getElementById("chatbot-body");
        
        // Clear existing options before adding new ones
        chatBody.innerHTML = "";

        // Show greeting
        appendChatMessage("bot", `Here are some things I can help with:`);

        let options = Object.keys(predefinedResponses);

        options.forEach((option, index) => {
            let optionDiv = document.createElement("div");
            optionDiv.className = "chatbot-option";
            optionDiv.innerText = option;
            optionDiv.onclick = () => handleUserQuery(option);
            chatBody.appendChild(optionDiv);
        });

        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function handleUserQuery(userMessage) {
        if (predefinedResponses[userMessage]) {
            appendChatMessage("bot", predefinedResponses[userMessage]);
        } else {
            getAIResponse(userMessage);
        }
    }

    async function getAIResponse(userMessage) {
        appendChatMessage("bot", "Thinking... 🤔");

        try {
            const response = await fetch(API_URL, {
                method: "POST",
                headers: {
                    "Authorization": `Bearer ${API_KEY}`,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    inputs: userMessage,
                    parameters: { max_new_tokens: 150 }
                })
            });

            const data = await response.json();
            let aiResponse = data?.generated_text || data[0]?.generated_text || "Sorry, I didn't understand that.";

            document.querySelector(".chatbot-bot-message:last-child").remove();
            appendChatMessage("bot", aiResponse);

        } catch (error) {
            document.querySelector(".chatbot-bot-message:last-child").remove();
            appendChatMessage("bot", "Sorry, I couldn't connect to AI. Try again later.");
            console.error("Error fetching AI response:", error);
        }
    }
</script>

