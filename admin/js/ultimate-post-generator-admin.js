(function() {
	document.addEventListener('DOMContentLoaded', function() {
		/**
		 * AJAX Messages
		 */
		function showMessage(message, success = true) {
			const messageDiv = document.createElement('div');
			messageDiv.style.position = 'fixed';
			messageDiv.style.top = '45px';
			messageDiv.style.right = '20px';
			messageDiv.style.backgroundColor = success ? '#edfbf6' : '#fef4f6';
			messageDiv.style.color = success ? '#32d296' : '#f0506e';
			messageDiv.style.padding = '15px 29px 15px 15px';
			messageDiv.style.borderRadius = '5px';
			messageDiv.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
			messageDiv.style.zIndex = '10001';
			messageDiv.innerHTML = `<span style="font-size: 16px;">${message}</span>`;
		
			document.body.appendChild(messageDiv);
		
			setTimeout(() => {
				messageDiv.style.opacity = '0';
				setTimeout(() => messageDiv.remove(), 1000);
			}, 5000);
		}
		
		/**
		 * Code in charge of the Post maker
		 */
		const form = document.querySelector('.prompt-form form');
	
		const insertContentIntoTinyMCE = (content) => {
			if (window.tinymce) {
				const editor = tinymce.get('upg-openai-editor');
				if (editor) {
					editor.setContent(content);
				} else {
					// Fallback if TinyMCE editor is not yet initialized
					document.getElementById('upg-openai-editor').value = content;
				}
			}
		}

		// Function to check and load data from localStorage
		const loadResponseFromLocalStorage = () => {
			const storedData = localStorage.getItem('openai_response');
			if (storedData) {
				const data = JSON.parse(storedData);
				const isExpired = new Date(data.expiry) < new Date();
				if (!isExpired) {
					insertContentIntoTinyMCE(data.message);
					form.style.display = 'none'; // Reduce opacity
					form.style.pointerEvents = 'none'; // Disable form
				}
			}
		}

		// Checking localStorage for existing data
		if (form) loadResponseFromLocalStorage();
		
		// Handle submit to OpenIA
		const handleFormSubmit = (event) => {
			event.preventDefault();
			const userInput = document.getElementById('upg-post-input').value;
			const loadingIndicator = document.getElementById('loading-indicator'); // Reference to loading indicator
			loadingIndicator.style.display = 'block'; // Show loading indicator

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
				loadingIndicator.style.display = 'none'; // Hide loading indicator
				if (data.success) {
					// Store response in localStorage with an expiry date
					localStorage.setItem('openai_response', JSON.stringify({
						message: data.data.message,
						expiry: new Date(new Date().getTime() + (15 * 24 * 60 * 60 * 1000)) // 15 days from now
					}));

					insertContentIntoTinyMCE(data.data.message);
					form.style.opacity = '0.5'; // Reduce opacity
					form.style.pointerEvents = 'none'; // Disable form
					loadingIndicator.style.display = 'none'; // Hide loading indicator
				} else {
					console.error(data.error);
				}
			})
			.catch(error => {
				console.error('Error:', error);
				loadingIndicator.style.display = 'none'; // Hide loading indicator
			});
		}
		
		if (form) form.addEventListener('submit', handleFormSubmit);

		// Add functionality for 'Start a New Post' button
        const startNewPostButton = document.querySelector('.start-new');
		const handleStartNewPost = () => {
			if (window.tinymce) {
				const editor = tinymce.get('upg-openai-editor');
				if (editor) {
					editor.setContent('');
				}
			}
			localStorage.removeItem('openai_response');
			form.style.display = ''; // Show the form again
			form.style.opacity = '1';
			form.style.pointerEvents = 'auto';
		}
		if (startNewPostButton) startNewPostButton.addEventListener('click', handleStartNewPost);
        

		// Save content as a post draft
		const saveDraftButton = document.querySelector('.save-draft');
		const handleSaveDraft = () => {
			const editorContent = window.tinymce ? tinymce.get('upg-openai-editor').getContent() : '';
			fetch(open_ai_script_data.ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					'action': 'upg_save_draft_post',
					'post_content': editorContent,
					'nonce': open_ai_script_data.nonce
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					console.log('Draft saved successfully');
					// Additional success handling can go here
				} else {
					console.error('Error saving draft:', data.error);
					// Additional error handling can go here
				}
			})
			.catch(error => console.error('Error:', error));
		}
		if (saveDraftButton) saveDraftButton.addEventListener('click', handleSaveDraft);

		// Delete prompt
		const deleteButtons = document.querySelectorAll('.delete-prompt');
		const handleDeletePrompt = (button) => {
			// Logic for deleting a prompt
			const row = button.closest('tr');
			const promptIndex = row.getAttribute('data-prompt-index');
	
			// Confirm before deleting
			if (confirm('Are you sure you want to delete this prompt?')) {
				fetch(open_ai_script_data.ajax_url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						'action': 'upg_delete_custom_prompt',
						'prompt_index': promptIndex,
						'nonce': open_ai_script_data.nonce
					})
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						showMessage('Prompt deleted successfully');
						row.remove(); // Remove the row from the table
					} else {
						console.error('Error deleting prompt:', data.error);
						showMessage(`Error deleting prompt ${data.error}`, false);
					}
				})
				.catch(error => console.error('Error:', error));
			}
		}
		if (deleteButtons.length) deleteButtons.forEach(button => button.addEventListener('click', () => handleDeletePrompt(button)));
		
		// Update/Edit prompts
		const editPromptModal = document.getElementById('edit-prompt-modal');
		const editTitleInput = document.getElementById('edit-prompt-title');
		const editDescriptionInput = document.getElementById('edit-prompt-description');
		const updateButton = document.getElementById('update-prompt');
		const cancelModal = document.getElementById('cancel-update-prompt');
		const closeModal = document.getElementById('close-modal-prompt');

		const editButtons = document.querySelectorAll('.edit-prompt');
		const handleEditPrompt = (button) => {
			const row = button.closest('tr');
			const title = row.querySelector('td:nth-child(1)').textContent;
			const description = row.querySelector('td:nth-child(2)').textContent;
			const promptIndex = row.getAttribute('data-prompt-index');

			editTitleInput.value = title;
			editDescriptionInput.value = description;
			editPromptModal.dataset.promptIndex = promptIndex;

			editPromptModal.classList.add('active');
		}
		if (editButtons.length) editButtons.forEach(button => button.addEventListener('click', () => handleEditPrompt(button)));
		if (cancelModal) cancelModal.addEventListener('click', () => editPromptModal.classList.remove('active'));
		if (closeModal ) closeModal .addEventListener('click', () => editPromptModal.classList.remove('active'));
		
		const handleUpdatePrompt = () => {
			const updatedTitle = editTitleInput.value;
			const updatedDescription = editDescriptionInput.value;
			const promptIndex = editPromptModal.dataset.promptIndex;

			fetch(open_ai_script_data.ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					'action': 'upg_update_custom_prompt',
					'prompt-title': updatedTitle,
					'prompt-description': updatedDescription,
					'prompt_index': promptIndex,
					'nonce': open_ai_script_data.nonce
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					showMessage('The Prompt was updated successfully');
					// Update logic and modal hide
				} else {
					showMessage(`Error updating prompt ${data.error}`, false);
					console.error('Error updating prompt:', data.error);
				}
			})
			.catch(error => console.error('Error:', error));
		}
		if (updateButton) updateButton.addEventListener('click', handleUpdatePrompt);

		// Update input with Prompts
		var prompts = document.forms['upg-default-prompts'];
		var inputField = document.getElementById('upg-post-input');
		if(prompts) {
			function updateInput(promptText) {
				inputField.placeholder = 'Write a ' + promptText;
				inputField.value = promptText;
			}
	
			// Set initial value and placeholder based on the checked radio button
			var checkedPrompt = prompts.querySelector('input[name="prompt"]:checked');
			if (checkedPrompt) {
				updateInput(checkedPrompt.value);
			}
	
			prompts.addEventListener('change', function(e) {
				if (e.target.name === 'prompt') {
					updateInput(e.target.value);
				}
			});
		}

		// Create Propmts
		const customPromptsForm = document.getElementById('custom-prompts-form');
		const handleCustomPromptSubmit = (event) => {
			event.preventDefault();
				
			const title = document.getElementById('prompt-title').value;
			const description = document.getElementById('prompt-description').value;

			fetch(open_ai_script_data.ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					'action': 'upg_save_custom_prompt',
					'prompt-title': title,
					'prompt-description': description,
					'nonce': open_ai_script_data.nonce
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					showMessage('The Prompt was created successfully');
					// Additional handling like clearing form or showing success message
				} else {
					console.error('Error saving prompt:', data.error);
					showMessage(`Error saving prompt ${data.error}`, false);
				}
			})
			.catch(error => console.error('Error:', error));
		}
		if (customPromptsForm) customPromptsForm.addEventListener('submit', handleCustomPromptSubmit);
		
	});
})();




