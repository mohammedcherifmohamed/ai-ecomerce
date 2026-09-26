// Automated Test Flow
const testFlow = async () => {
    console.log('Starting AI SQL Agent Test Flow...');
    
    // Step 1: Submit Query
    const submitResponse = await pm.sendRequest({
        url: `${pm.environment.get("base_url")}/api/ai/query`,
        method: 'POST',
        header: {
            'Authorization': `Bearer ${pm.environment.get("api_key")}`
        },
        body: {
            mode: 'raw',
            raw: JSON.stringify({
                query: "Show top 10 customers by total orders"
            })
        }
    });
    
    const jobId = submitResponse.json().job_id;
    console.log(`Job submitted: ${jobId}`);
    
    // Step 2: Poll for status
    let status = 'pending';
    let attempts = 0;
    const maxAttempts = 20;
    
    while (status === 'pending' || status === 'processing') {
        attempts++;
        await new Promise(resolve => setTimeout(resolve, 3000));
        
        const statusResponse = await pm.sendRequest({
            url: `${pm.environment.get("base_url")}/api/ai/query/${jobId}/status`,
            method: 'GET',
            header: {
                'Authorization': `Bearer ${pm.environment.get("api_key")}`
            }
        });
        
        status = statusResponse.json().status;
        console.log(`Attempt ${attempts}: Status = ${status}`);
        
        if (attempts >= maxAttempts) {
            console.log('Max attempts reached. Test failed.');
            return;
        }
    }
    
    // Step 3: Verify result
    if (status === 'completed') {
        const result = pm.response.json().result;
        console.log('Query completed successfully!');
        console.log('Result preview:', result.substring(0, 100) + '...');
        console.log('Test PASSED ✓');
    } else {
        console.log('Query failed:', pm.response.json().error);
        console.log('Test FAILED ✗');
    }
};

// Run the test
testFlow();