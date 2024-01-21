document.addEventListener('DOMContentLoaded', function() {
	const chatMessages = document.getElementById('upg-chat-messages');
    let conversation = JSON.parse(localStorage.getItem('chat_conversation')) || [];

	// Display existing conversation
	addToChat();

	const form = document.querySelector('.prompt-form form');
	if (form) {
		form.addEventListener('submit', function(event) {
			event.preventDefault();
			const userInput = document.getElementById('input').value;

			fetch(open_ai_script_data.ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					'action': 'get_openai_response',
					'input': userInput,
					'nonce': open_ai_script_data.nonce
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					console.log("data: ", data);
					conversation.push({ sender: 'user', message: userInput });
					// Access the message inside the data object
					const botMessage = data.data.message; 
					conversation.push({ sender: 'bot', message: botMessage });
					localStorage.setItem('chat_conversation', JSON.stringify(conversation));
					addToChat(conversation);
				} else {
					console.error(data.error);
				}
			})
			.catch(error => console.error('Error:', error));
		});
	} else {
		console.error('Form not found');
	}
	function addToChat() {
        chatMessages.innerHTML = ''; // Clear existing messages
        conversation.forEach(msg => {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add(msg.sender);
    
            // Basic formatting (example)
            if (msg.message.includes('Heading:')) {
                const heading = document.createElement('h2');
                heading.textContent = msg.message.replace('Heading:', '');
                messageDiv.appendChild(heading);
            } else if (msg.message.includes('List:')) {
                const listItems = msg.message.replace('List:', '').split(',');
                const list = document.createElement('ul');
                listItems.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.textContent = item.trim();
                    list.appendChild(listItem);
                });
                messageDiv.appendChild(list);
            } else {
                messageDiv.textContent = msg.message;
            }
            chatMessages.appendChild(messageDiv);
        });
        // Assuming the last message is the one from OpenAI
        const lastMsg = conversation[conversation.length - 1];
        if (lastMsg && lastMsg.sender === 'bot') {
            const wysiwygEditor = document.getElementById('upg-wysiwyg-editor');
            wysiwygEditor.innerHTML = messageDiv.innerHTML;
        }
    }
    
    function updateConversation(message) {
		conversation.push(message);
		localStorage.setItem('chat_conversation', JSON.stringify(conversation));
		addToChat();
	}
});