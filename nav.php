
<!-- Navigation Bar -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Popper.js -->
<script src="https://unpkg.com/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

<!-- Bootstrap JS (with Popper) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta3/js/bootstrap.bundle.min.js"></script>

<nav class="navbar navbar-expand-lg bg-nav">
  <div class="container">
    <img src="images/logo.jpg" class="rounded d-block" style="max-width: 1000px; height: 60px;" alt="Logo">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse cyberpunk-font" id="navbarNav">
      <ul class="navbar-nav ms-auto"> <!-- Changed ml-auto to ms-auto for Bootstrap 5 -->
        <li class="nav-item" id="home-item">
          <a class="nav-link" href="home.php">Home</a>
        </li>
        <li class="nav-item" id="about-item">
          <a class="nav-link" href="about.php">About</a>
        </li>
        <!-- Dropdown -->
        <li class="nav-item dropdown" id="products-item">
          <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Products
          </a>
          <ul class="dropdown-menu" aria-labelledby="productsDropdown">
            <li><a class="dropdown-item" href="products.php?category=CPU">Processor</a></li>
            <li><a class="dropdown-item" href="products.php?category=Motherboard">Motherboard</a></li>
            <li><a class="dropdown-item" href="products.php?category=Storage">Storage Device</a></li>
            <li><a class="dropdown-item" href="products.php?category=RAM">RAM</a></li>
            <li><a class="dropdown-item" href="products.php?category=GPU">Graphic Card</a></li>
            <li><a class="dropdown-item" href="products.php?category=PSU">PSU</a></li>
            <li><a class="dropdown-item" href="products.php?category=Mouse">Mouse</a></li>
            <li><a class="dropdown-item" href="products.php?category=Keyboard">Keyboard</a></li>
            <li><a class="dropdown-item" href="products.php?category=Monitor">Monitor</a></li>
          </ul>
        </li>
        <li class="nav-item" id="build-pc-item">
          <a class="nav-link" href="build_my_pc.php">Build My PC</a>
        </li>
        <li class="nav-item" id="blogs-item">
          <a class="nav-link" href="blogs.php">Blogs</a>
        </li>
        <li class="nav-item" id="forums-item">
          <a class="nav-link" href="forums.php">Forum</a>
        </li>
        <li class="nav-item" id="contact-item">
          <a class="nav-link" href="contact.php">Contact</a>
        </li>

        <!-- My Account -->
        <li class="nav-item" id="my-account-item">
          <a class="nav-link" href="my_account.php" id="login-link"><i class="bi bi-person"></i></a>
        </li>
        <!-- Compare Button -->
        <li class="nav-item">
          <a class="nav-link" href="compare.php" id="compare-link"><i class="bi bi-arrow-repeat text.white"></i></a>
        </li>
        <!-- Cart and Wishlist -->
        <li class="nav-item">
          <a class="nav-link" href="cart.php" id="cart-link"><i class="bi bi-cart"></i></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="wishlist.php" id="wishlist-link"><i class="bi bi-heart"></i></a>
        </li>
        <!-- Login/Logout Icon -->
        <li class="nav-item" id="login-logout-item">
          <a class="nav-link" href="#" id="login-logout-link">Log In</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script>
  $(document).ready(function() {
    // Set active class based on the current page
    var currentPage = window.location.pathname.split("/").pop();
    $("#" + currentPage.split(".")[0] + "-item").addClass("active");

    // Set login/logout link based on PHP session
    var isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

    if (isLoggedIn) {
      $("#login-logout-link").text("Log Out");
      $("#login-logout-link").attr("href", "logout.php");
    } else {
      $("#login-logout-link").text("Log In");
      $("#login-logout-link").attr("href", "login.php");
    }
  });
</script>

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
        } else if (!document.querySelector(".chatbot-message")) {
            setTimeout(() => {
                appendChatMessage("bot", `Welcome back, ${userName}! How can I support you today?`);
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
        let options = Object.keys(predefinedResponses);

        options.forEach((option, index) => {
            let optionDiv = document.createElement("div");
            optionDiv.className = "chatbot-option";
            optionDiv.innerText = option;
            optionDiv.onclick = () => handleUserQuery(option);  // Ensure predefined responses get handled correctly
            chatBody.appendChild(optionDiv);
        });

        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function handleUserQuery(userMessage) {
        // Check if userMessage is in predefined responses
        if (predefinedResponses[userMessage]) {
            appendChatMessage("bot", predefinedResponses[userMessage]);
        } else {
            getAIResponse(userMessage);  // Send to AI if not predefined
        }
    }

    async function getAIResponse(userMessage) {
        appendChatMessage("bot", "Thinking... 🤔"); // Placeholder while AI processes

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




